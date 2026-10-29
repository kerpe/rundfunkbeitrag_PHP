<?php

// Hostinger cron command: php /ABSOLUTE/PATH/private/cron.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
umask(0077);
require __DIR__.'/app/bootstrap.php';
function purge_stale_drafts(): int
{
    $n = 0;
    foreach (sql("SELECT id FROM rb_records WHERE kind='case' AND owner_hash<>'erasing' AND updated_at<? AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.state'))='draft' LIMIT 200", [time() - 30 * 86400])->fetchAll() as $row) {
        db()->beginTransaction();
        $r = record($row['id'], true);
        if (!$r || $r['value']['state'] !== 'draft' || (int)$r['updated_at'] >= time() - 30 * 86400) {
            db()->commit();
            continue;
        }
        $c = $r['value'];
        sql('DELETE FROM rb_records WHERE parent_id=? OR id=?', [$c['id'], 'case:'.$c['id']]);
        db()->commit();
        foreach ($c['documents'] as $d) {
            $f = private_root().'/data/uploads/'.$d['objectKey'];
            if (is_file($f)) {
                unlink($f);
            }
        }
        $n++;
    }
    return $n;
}
// Orders that were never submitted (e.g. missing details never arrived) are deleted after STALE_ORDER_DAYS without any change
// (privacy notice §11); invoices are kept for accounting.
const STALE_ORDER_DAYS = 180;
function purge_stale_orders(): int
{
    $n = 0;
    foreach (sql("SELECT id FROM rb_records WHERE kind='case' AND owner_hash<>'erasing' AND updated_at<? AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.state')) IN ('ordered','in_review','needs_information') LIMIT 200", [time() - STALE_ORDER_DAYS * 86400])->fetchAll() as $row) {
        db()->beginTransaction();
        $r = record($row['id'], true);
        $c = $r['value'] ?? null;
        if (!$c || !in_array($c['state'], ['ordered', 'in_review', 'needs_information'], true) || !empty($c['submission']) || (int)$r['updated_at'] >= time() - STALE_ORDER_DAYS * 86400) {
            db()->commit();
            continue;
        }
        sql("DELETE FROM rb_records WHERE (parent_id=? AND kind<>'invoice') OR id=?", [$c['id'], 'case:'.$c['id']]);
        put('erasure:'.$c['id'], 'erasure', ['id' => $c['id'], 'at' => now(), 'by' => 'stale-order', 'status' => 'complete']);
        db()->commit();
        foreach ($c['documents'] as $d) {
            if (($d['objectKey'] ?? '') !== '' && is_file($f = private_root().'/data/uploads/'.$d['objectKey'])) {
                unlink($f);
            }
        }
        $n++;
    }
    return $n;
}
// Retention (privacy notice §11): customer-uploaded evidence goes DOC_KEEP_DAYS after submission (or closing); the whole case CASE_KEEP_DAYS later. Staff receipts stay until then; invoices are kept for accounting.
const DOC_KEEP_DAYS = 180;
const CASE_KEEP_DAYS = 1095;
function retention_base(array $c): int
{
    return strtotime($c['submission']['at'] ?? $c['updatedAt']) ?: time();
}

function purge_retention(): array
{
    $docs = 0;
    $cases = 0;
    foreach (sql("SELECT id FROM rb_records WHERE kind='case' AND owner_hash<>'erasing' AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.state')) IN ('submitted','closed') LIMIT 500")->fetchAll() as $row) {
        db()->beginTransaction();
        $r = record($row['id'], true);
        $c = $r['value'] ?? null;
        if (!$c || !in_array($c['state'], ['submitted', 'closed'], true)) {
            db()->commit();
            continue;
        }
        $base = retention_base($c);
        $files = [];
        if ($base < time() - CASE_KEEP_DAYS * 86400) {
            foreach ($c['documents'] as $d) {
                $files[] = $d['objectKey'] ?? '';
            }
            sql("DELETE FROM rb_records WHERE (parent_id=? AND kind<>'invoice') OR id=?", [$c['id'], 'case:'.$c['id']]);
            put('erasure:'.$c['id'], 'erasure', ['id' => $c['id'], 'at' => now(), 'by' => 'retention', 'status' => 'complete']);
            $cases++;
        } elseif ($base < time() - DOC_KEEP_DAYS * 86400 && empty($c['documentsPurgedAt'])) {
            $keep = [];
            foreach ($c['documents'] as $d) {
                if (in_array($d['kind'], ['submissionReceipt', 'officialResponse'], true)) {
                    $keep[] = $d;
                    continue;
                }
                $files[] = $d['objectKey'] ?? '';
                sql('DELETE FROM rb_records WHERE id=?', ['document:'.$d['id']]);
                $docs++;
            }
            $c['documents'] = $keep;
            $c['documentsPurgedAt'] = now();
            $c['version']++;
            $c['updatedAt'] = now();
            put('case:'.$c['id'], 'case', $c, '', $r['owner_hash']);
        }
        db()->commit();
        foreach ($files as $f) {
            if ($f !== '' && is_file($file = private_root().'/data/uploads/'.$f)) {
                unlink($file);
            }
        }
    }
    return ['documents' => $docs, 'cases' => $cases];
}
// Payment reminders (AGB 6.3): up to three free emails while an order is unpaid, 3, 10 and 21 days after ordering.
// The third one says it is the last; the case is then marked unpaid for staff and nothing else happens.
// Orders placed before the feature went live get none.
const REMINDER_DAYS = [3, 10, 21];
const REMINDER_SINCE = '2026-10-29T00:00:00Z';
function payment_reminders(): int
{
    $n = 0;
    foreach (sql("SELECT id FROM rb_records WHERE kind='case' AND owner_hash<>'erasing' AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.state')) IN ('ordered','in_review','needs_information','submitting','submitted','closed') LIMIT 500")->fetchAll() as $row) {
        db()->beginTransaction();
        $r = record($row['id'], true);
        $c = $r['value'] ?? null;
        $at = strtotime($c['order']['at'] ?? '') ?: 0;
        $sent = $c['operations']['paymentReminders'] ?? (isset($c['operations']['paymentReminder']) ? [$c['operations']['paymentReminder']] : []);
        $k = count($sent);
        $last = $k ? (strtotime($sent[$k - 1]['at'] ?? '') ?: 0) : 0;
        if (!$c || !$at || $at < strtotime(REMINDER_SINCE) || $k >= count(REMINDER_DAYS) || $at > time() - REMINDER_DAYS[$k] * 86400 || $last > time() - 3 * 86400 || !empty($c['checks']['paid']) || isset($c['operations']['withdrawal']) || !filter_var($c['data']['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            db()->commit();
            continue;
        }
        payment_reminder($c, $k + 1, count(REMINDER_DAYS));
        $sent[] = ['at' => now()];
        $c['operations']['paymentReminders'] = $sent;
        unset($c['operations']['paymentReminder']);
        if ($k + 1 === count(REMINDER_DAYS)) {
            $c['operations']['unpaid'] = ['at' => now()];
        }
        $c['version']++;
        $c['updatedAt'] = now();
        put('case:'.$c['id'], 'case', $c, '', $r['owner_hash']);
        db()->commit();
        $n++;
    }
    return $n;
}
try {
    $reminders = payment_reminders();
    $result = dispatch_mail();
    $purged = purge_stale_drafts();
    $staleOrders = purge_stale_orders();
    $retention = purge_retention();
    sql('DELETE FROM rb_records WHERE expires_at IS NOT NULL AND expires_at<?', [time()]);
    put('settings:jobs', 'settings', ['updated_at' => now(), 'delivery' => $result, 'paymentReminders' => $reminders, 'draftsPurged' => $purged, 'staleOrdersPurged' => $staleOrders, 'retention' => $retention]);
    echo "Mail queue processed.\n";
} catch (Throwable) {
    fwrite(STDERR, "Mail job failed; check administrator delivery tools.\n");
    exit(1);
}
