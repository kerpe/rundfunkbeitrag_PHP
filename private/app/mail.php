<?php

function mail_ready(): bool
{
    $c = config();
    return !empty($c['resend_key']) && !empty($c['email_from']);
}

function queue_mail(string $id, array $c, string $kind, string $to, array $payload): void
{
    if (record('mail:'.$id)) {
        return;
    }
    put('mail:'.$id, 'mail', ['id' => $id, 'case_id' => $c['id'], 'kind' => $kind, 'recipient' => $to, 'mode' => 'live', 'payload' => $payload, 'status' => 'queued', 'attempts' => 0, 'first_attempt_at' => null, 'next_attempt_at' => 0, 'provider_id' => null, 'error' => null, 'created_at' => now(), 'delivered_at' => null], $c['id']);
}

function queue_notice(array $c, string $action, string $id): void
{
    if (!$c['order']) {
        return;
    }
    $o = $c['order'];
    $l = rb_lang($o['language'] ?? 'de');
    $title = mt(isset(RB_TEXT['de']['title.'.$action]) ? 'title.'.$action : 'title.update', $l);
    $paras = isset(RB_TEXT['de']['step.'.$action]) ? mt('step.'.$action, $l) : [];
    if ($action === 'order' && ($c['data']['payMethod'] ?? '') === 'debit') {
        $paras = array_merge((array)$paras, [mt('order.debit', $l)]);
    }
    if ($action === 'order' && $c['route'] === 'death' && ($c['data']['remaining'] ?? '') === 'yes' && ($c['data']['remainingWho'] ?? '') === 'other') {
        $paras = array_merge((array)$paras, [mt('order.remainingOther', $l, ['name' => trim(($c['data']['remainingFirst'] ?? '').' '.($c['data']['remainingLast'] ?? ''))])]);
    }
    $invoice = $action === 'payment-received' && record('invoice:'.$c['id']);
    if ($invoice) {
        $paras = array_merge((array)$paras, [mt('inv.mail', $l)]);
    }
    if ($action === 'record-submission' && !empty($c['submission']['at']) && $paras) {
        $w = (new DateTimeImmutable($c['submission']['at']))->setTimezone(new DateTimeZone('Europe/Berlin'));
        array_splice($paras, 1, 0, [mt('submission.when', $l, ['date' => $w->format('d.m.Y'), 'time' => $w->format('H:i')])]);
    }
    $email = strtolower(trim($c['data']['email']));
    $link = config()['public_url'].'/recover-access';
    if (!record('mail:'.$id)) {
        $link = case_link($c['id'], $email, $o['reference']);
    }
    $p = ['subject' => $title.' · '.$o['reference'], 'text' => $title."\n".$o['reference']."\n\n".($paras ? implode("\n\n", $paras)."\n\n" : '').mt('case.linkIntro', $l)."\n".$link, 'view' => ['lang' => $l, 'title' => $title, 'ref' => $o['reference'], 'paras' => $paras, 'link' => $link, 'linkNote' => mt('case.linkNote', $l)], 'reply_to' => support_email()];
    if ($action === 'order') {
        $p['snapshot'] = $c;
    }
    if ($invoice) {
        $p['invoice'] = $c['id'];
    }
    queue_mail($id, $c, $action, $email, $p);
}
// Order confirmation: payment details first, then the case link built by queue_notice, in the order's language.
function order_mail(array $p, array $snapshot): array
{
    $o = $snapshot['order'];
    $l = rb_lang($o['language'] ?? 'de');
    $amount = mail_money((int)($o['feeCents'] ?? 4900), $l);
    $intro = mt('pay.intro', $l, ['amount' => $amount]);
    $p['text'] = mb_strtoupper(mt('pay.title', $l))."\n\n".$intro."\n\n".mt('pay.transfer', $l).":\n".mt('pay.recipient', $l).': '.$o['beneficiary']."\nIBAN: ".$o['iban']."\nBIC: ".$o['bic']."\n".mt('pay.amount', $l).': '.$amount."\n".mt('pay.reference', $l).': '.$o['reference']."\n\n".mt('pay.referenceNote', $l)."\n".(girocode_payload($o) ? "\n".mt('pay.girocodeText', $l)."\n" : '')."\n----\n\n".$p['text'];
    if (isset($p['view'])) {
        $p['view'] = ['lang' => $l, 'title' => mt('pay.title', $l), 'preheader' => mt('pay.preheader', $l).' · '.$o['reference'], 'ref' => $o['reference'], 'paras' => [$intro], 'pay' => ['beneficiary' => $o['beneficiary'], 'iban' => $o['iban'], 'bic' => $o['bic'], 'feeCents' => $o['feeCents'] ?? 4900, 'amount' => $amount, 'service' => pick_lang(rules()['routes'][$snapshot['route']]['title'], $l), 'at' => gmdate('d.m.Y, H:i', strtotime($o['at'])).' UTC']] + $p['view'];
    }
    return $p;
}

// Payment reminder number $no of $of, sent by cron.php while an order is unpaid; same layout as the order confirmation, without the PDF.
// The last one says politely that we will not write again about this payment.
function payment_reminder(array $c, int $no = 1, int $of = 1): void
{
    $o = $c['order'];
    $l = rb_lang($o['language'] ?? 'de');
    $email = strtolower(trim($c['data']['email']));
    $amount = mail_money((int)($o['feeCents'] ?? 4900), $l);
    $at = (new DateTimeImmutable($o['at']))->setTimezone(new DateTimeZone('Europe/Berlin'))->format('d.m.Y');
    $final = $no >= $of;
    $title = mt($final ? 'rem.titleLast' : 'rem.title', $l);
    $paras = [mt($final ? 'rem.introLast' : ($no > 1 ? 'rem.intro2' : 'rem.intro'), $l, ['date' => $at, 'amount' => $amount])];
    $link = case_link($c['id'], $email, $o['reference']);
    $text = $title."\n".$o['reference']."\n\n".$paras[0]."\n\n".mt('pay.transfer', $l).":\n".mt('pay.recipient', $l).': '.$o['beneficiary']."\nIBAN: ".$o['iban']."\nBIC: ".$o['bic']."\n".mt('pay.amount', $l).': '.$amount."\n".mt('pay.reference', $l).': '.$o['reference']."\n\n".mt('pay.referenceNote', $l)."\n\n".mt('rem.paid', $l)."\n\n".mt('rem.free', $l)."\n\n".mt('case.linkIntro', $l)."\n".$link;
    $view = ['lang' => $l, 'title' => $title, 'preheader' => mt('rem.preheader', $l).' · '.$o['reference'], 'ref' => $o['reference'], 'paras' => $paras, 'noPdf' => true, 'pay' => ['beneficiary' => $o['beneficiary'], 'iban' => $o['iban'], 'bic' => $o['bic'], 'feeCents' => $o['feeCents'] ?? 4900, 'amount' => $amount, 'service' => pick_lang(rules()['routes'][$c['route']]['title'], $l), 'at' => gmdate('d.m.Y, H:i', strtotime($o['at'])).' UTC'], 'link' => $link, 'linkNote' => mt('case.linkNote', $l), 'after' => [mt('rem.paid', $l), mt('rem.free', $l)]];
    queue_mail('payment-reminder-'.$c['id'].($no > 1 ? '-'.$no : ''), $c, 'payment-reminder', $email, ['subject' => mt($final ? 'rem.subjectLast' : 'rem.subject', $l).' · '.$o['reference'], 'text' => $text, 'view' => $view, 'reply_to' => support_email()]);
}

function case_link(string $caseId, string $email, string $ref): string
{
    $raw = bin2hex(random_bytes(32));
    $exp = time() + 14 * 86400;
    put('token:'.secret_hash($raw), 'token', ['caseId' => $caseId, 'email' => $email, 'kind' => 'mail', 'expires' => $exp], $caseId, '', $exp);
    return config()['public_url'].'/case-access?ref='.rawurlencode($ref).'#'.$raw;
}

function staff_message_notice(array $c, string $id): void
{
    $to = strtolower(trim(settings()['supportEmail'] ?? ''));
    if (!$c['order'] || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $ref = $c['order']['reference'];
    $route = rules()['routes'][$c['route']]['title']['de'] ?? $c['route'];
    $name = trim(($c['data']['firstName'] ?? '').' '.($c['data']['lastName'] ?? ''));
    queue_mail($id.'-staff', $c, 'staff-message', $to, ['subject' => 'Neue Kundennachricht / New customer message · '.$ref, 'text' => "Neue Kundennachricht / New customer message\n{$ref} · {$route} · {$name} · {$c['data']['email']}\n\n".$c['operations']['complaint']['message']."\n\nAntworten Sie bei Bedarf per E-Mail an die Kundin oder den Kunden. / Reply to the customer by email if needed.\n".config()['public_url'].'/admin']);
}

function staff_review_notice(array $c, string $id): void
{
    $to = strtolower(trim(settings()['supportEmail'] ?? ''));
    if (!$c['order'] || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $r = $c['operations']['review'];
    $ref = $c['order']['reference'];
    queue_mail($id.'-staff', $c, 'staff-message', $to, ['subject' => 'Neue Bewertung / New review · '.$ref.' · '.$r['rating'].'/5', 'text' => "Neue Kundenbewertung / New customer review\n{$ref} · {$r['rating']}/5 · {$r['name']}".($r['place'] !== '' ? ' · '.$r['place'] : '')."\n\n{$r['text']}\n\nErst nach Ihrer Freigabe im Vorgang sichtbar. / Shown only after you approve it in the case.\n".config()['public_url'].'/admin']);
}

// Contact form: goes to the support address only; replying answers the customer directly.
function contact_mail(string $name, string $email, string $ref, string $message): void
{
    $to = strtolower(trim(settings()['supportEmail'] ?? ''));
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        fail('SERVICE_UNAVAILABLE', 503);
    }
    $who = ($name !== '' ? $name.' · ' : '').$email.($ref !== '' ? ' · '.$ref : '');
    queue_mail(uuid().'-contact', ['id' => ''], 'staff-message', $to, ['subject' => 'Kontaktformular / Contact form · '.($name !== '' ? $name : $email), 'text' => "Kontaktformular / Contact form\n{$who}\nSprache / Language: ".request_lang()."\n\n{$message}\n\nAntworten Sie direkt auf diese E-Mail. / Reply directly to this email.", 'reply_to' => $email]);
}

// Withdrawal form: staff always hear about it; a declaration we could not match to an order gets its own receipt.
function withdrawal_mails(array $ack, string $op, ?array $match): void
{
    $to = strtolower(trim(settings()['supportEmail'] ?? ''));
    $case = ['id' => $match['id'] ?? ''];
    $who = $ack['name'].' · '.$ack['email'];
    $at = gmdate('d.m.Y, H:i', strtotime($ack['at'])).' UTC';
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $what = $match ? 'Zugeordnet zu / Matched to '.$ack['reference'] : 'NICHT zugeordnet, bitte Auftrag suchen / NOT matched, please find the order'.($ack['reference'] !== '' ? ' (angegeben / given: '.$ack['reference'].')' : '');
        queue_mail($op.'-staff', $case, 'staff-message', $to, ['subject' => 'Widerruf eingegangen / Withdrawal received'.($match ? ' · '.$ack['reference'] : ''), 'text' => "Widerruf eingegangen / Withdrawal received\n{$at}\n{$who}\n{$what}".($ack['details'] !== '' ? "\n\nAngaben zum Vertrag / Contract details:\n".$ack['details'] : '')."\n\n".config()['public_url'].'/admin']);
    }
    if ($match) {
        return;
    }
    try {
        rate('withdrawal-receipt:'.$ack['email'], 3, 86400);
    } catch (AppError) {
        return;
    }
    $l = request_lang();
    $title = mt('title.withdraw', $l);
    $paras = $l === 'en'
        ? array_merge(['We received your withdrawal declaration on '.$at.'.', 'Your declaration: I hereby withdraw from the contract identified above.', 'Name: '.$ack['name']], $ack['reference'] !== '' ? ['Order number given: '.$ack['reference']] : [], $ack['details'] !== '' ? ['Your details: '.$ack['details']] : [], ['We will match the declaration to your order and contact you if we need anything else.'])
        : array_merge(['Ihre Widerrufserklärung ist am '.$at.' bei uns eingegangen.', 'Ihre Erklärung: Hiermit widerrufe ich den oben bezeichneten Vertrag.', 'Name: '.$ack['name']], $ack['reference'] !== '' ? ['Angegebene Auftragsnummer: '.$ack['reference']] : [], $ack['details'] !== '' ? ['Ihre Angaben: '.$ack['details']] : [], ['Wir ordnen die Erklärung Ihrem Auftrag zu und melden uns, falls wir noch etwas brauchen.']);
    queue_mail($op.'-receipt', $case, 'withdrawal-receipt', $ack['email'], ['subject' => $title, 'text' => $title."\n\n".implode("\n\n", $paras), 'view' => ['lang' => $l, 'title' => $title, 'paras' => $paras]]);
}

function access_mail(array $c, string $kind): string
{
    $email = email($c['data']['email'] ?? null);
    if ($kind === 'verify') {
        return verify_code_mail($c, $email);
    }
    rate($kind.':'.$email, 3, 3600);
    $raw = bin2hex(random_bytes(32));
    $id = uuid();
    put('token:'.secret_hash($raw), 'token', ['caseId' => $c['id'], 'email' => $email, 'kind' => $kind, 'expires' => time() + 1800], $c['id'], '', time() + 1800);
    $url = config()['public_url'].'/case-access#'.$raw;
    $l = request_lang();
    queue_mail($id, $c, $kind, $email, ['subject' => mt('access.title', $l), 'text' => mt('access.body', $l)."\n\n".$url."\n\n".mt('access.note', $l), 'view' => ['lang' => $l, 'title' => mt('access.title', $l), 'paras' => [mt('access.body', $l)], 'link' => $url, 'linkNote' => mt('access.note', $l)], 'expiresAt' => time() + 1800]);
    return $id;
}

function verify_code_mail(array $c, string $email): string
{
    rate('verify:'.$email, 5, 3600);
    $code = sprintf('%06d', random_int(0, 999999));
    $id = uuid();
    put('vcode:'.$c['id'], 'vcode', ['hash' => secret_hash($c['id'].':'.$code), 'email' => $email, 'attempts' => 0], $c['id'], '', time() + 1800);
    $l = request_lang();
    queue_mail($id, $c, 'verify', $email, ['subject' => mt('verify.subject', $l, ['code' => $code]), 'text' => mt('verify.title', $l).":\n\n{$code}\n\n".mt('verify.use', $l)."\n\n".mt('verify.ignore', $l), 'view' => ['lang' => $l, 'title' => mt('verify.title', $l), 'code' => $code, 'preheader' => mt('verify.preheader', $l, ['code' => $code]), 'after' => [mt('verify.use', $l), mt('verify.ignore', $l)]], 'expiresAt' => time() + 1800]);
    return $id;
}

function check_verify_code(array $c, mixed $input): void
{
    $code = preg_replace('/\D/', '', is_scalar($input) ? (string)$input : '');
    $email = strtolower(trim((string)($c['data']['email'] ?? '')));
    $r = record('vcode:'.$c['id'], true);
    if (!$r || $r['expires_at'] < time() || $r['value']['email'] !== $email || $r['value']['attempts'] >= 5) {
        fail('CODE_EXPIRED', 410);
    }
    $v = $r['value'];
    if (strlen($code) !== 6 || !hash_equals($v['hash'], secret_hash($c['id'].':'.$code))) {
        $v['attempts']++;
        put($r['id'], 'vcode', $v, $c['id'], '', (int)$r['expires_at']);
        db()->commit();
        fail($v['attempts'] >= 5 ? 'CODE_EXPIRED' : 'CODE_INVALID', $v['attempts'] >= 5 ? 410 : 422);
    }
    put('contact:'.$c['id'], 'contact', ['email' => $email, 'verifiedAt' => now()], $c['id']);
    sql('DELETE FROM rb_records WHERE id=?', [$r['id']]);
}

function remote_http(string $url, string $method = 'GET', ?array $payload = null, array $headers = []): array
{
    if (defined('RB_TEST_MAIL_CAPTURE') && str_starts_with($url, 'https://api.resend.com/')) {
        if ($payload) {
            file_put_contents(RB_TEST_MAIL_CAPTURE, json_encode($payload, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
        }
        return [200, ['id' => uuid(), 'last_event' => 'delivered']];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 12, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_HTTPHEADER => $headers]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($raw === false) {
        throw new RuntimeException('PROVIDER_UNAVAILABLE');
    }
    return [$code, json_decode($raw, true) ?? []];
}

function dispatch_mail(int $limit = 5): array
{
    if (!mail_ready()) {
        return ['enabled' => false, 'processed' => 0];
    }
    $processed = 0;
    foreach (array_map(fn ($r) => json_decode($r['data_json'], true), sql("SELECT data_json FROM rb_records WHERE kind='mail' AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.status')) IN ('queued','retry','sending') AND CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.next_attempt_at')), '0') AS UNSIGNED)<=UNIX_TIMESTAMP() AND (JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.status'))<>'sending' OR CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.lease_until')), '0') AS UNSIGNED)<=UNIX_TIMESTAMP()) ORDER BY created_at,id LIMIT 50")->fetchAll()) as $candidate) {
        if ($processed >= $limit) {
            break;
        }
        db()->beginTransaction();
        $r = record('mail:'.$candidate['id'], true);
        if (!$r) {
            db()->commit();
            continue;
        }
        $m = $r['value'];
        $t = time();
        if (!in_array($m['status'], ['queued', 'retry', 'sending'], true) || ($m['status'] === 'sending' && ($m['lease_until'] ?? 0) > $t) || $m['next_attempt_at'] > $t) {
            db()->commit();
            continue;
        }
        if (($m['payload']['expiresAt'] ?? PHP_INT_MAX) < $t) {
            $m['status'] = 'expired';
            put('mail:'.$m['id'], 'mail', $m, $m['case_id']);
            db()->commit();
            continue;
        }
        if ($m['attempts'] >= 6 || ($m['first_attempt_at'] !== null && $t - $m['first_attempt_at'] >= 23 * 3600)) {
            $m['status'] = 'needs_review';
            put('mail:'.$m['id'], 'mail', $m, $m['case_id']);
            db()->commit();
            continue;
        }
        $c = record('case:'.$m['case_id']);
        // Mail for an unmatched withdrawal has no case: only the receipt and the staff notice may go out without one.
        $caseless = ($m['case_id'] ?? '') === '' && in_array($m['kind'], ['withdrawal-receipt', 'staff-message'], true);
        if ($caseless ? $m['kind'] === 'staff-message' && strtolower(trim(settings()['supportEmail'] ?? '')) !== $m['recipient'] : ($m['kind'] === 'staff-invite' ? !record('staff:'.$m['recipient']) : !$c || $c['owner_hash'] === 'erasing' || ($m['kind'] === 'staff-message' ? strtolower(trim(settings()['supportEmail'] ?? '')) !== $m['recipient'] : (strtolower(trim($c['value']['data']['email'])) !== $m['recipient'] || (!in_array($m['kind'], ['verify', 'recover'], true) && (!record('contact:'.$m['case_id']) || record('contact:'.$m['case_id'])['value']['email'] !== $m['recipient'])))))) {
            $m['status'] = 'cancelled';
            put('mail:'.$m['id'], 'mail', $m, $m['case_id']);
            db()->commit();
            continue;
        }
        $lease = uuid();
        $m['lease'] = $lease;
        $m['lease_until'] = $t + 120;
        $m['status'] = 'sending';
        $m['attempts']++;
        $m['first_attempt_at'] ??= $t;
        put('mail:'.$m['id'], 'mail', $m, $m['case_id']);
        db()->commit();
        try {
            $p = $m['payload'];
            if (isset($p['snapshot']) && !isset($p['attachments'])) {
                $snapshot = $p['snapshot'];
                $html = document_html($snapshot);
                try {
                    $p['attachments'] = [['filename' => $snapshot['order']['reference'].'-'.mt('pdf.file', rb_lang($snapshot['order']['language'] ?? 'de')).'.pdf', 'content' => base64_encode(pdf_bytes($html))]];
                } catch (AppError $e) {
                    if ($e->reason !== 'PDF_USE_HTML') {
                        throw $e;
                    }
                    $p['attachments'] = [['filename' => $snapshot['order']['reference'].'-contract.html', 'content' => base64_encode($html)]];
                }
                $p = order_mail($p, $snapshot);
                unset($p['snapshot']);
                db()->beginTransaction();
                $check = record('mail:'.$m['id'], true);
                if (($check['value']['lease'] ?? '') !== $lease) {
                    fail('MAIL_LEASE_LOST', 409);
                }
                $m['payload'] = $p;
                put('mail:'.$m['id'], 'mail', $m, $m['case_id']);
                db()->commit();
            }
            if (isset($p['invoice']) && !isset($p['attachments'])) {
                $inv = record('invoice:'.$p['invoice']);
                if (!$inv) {
                    fail('DOCUMENT_NOT_FOUND', 404);
                }
                $p['attachments'] = [invoice_attachment($inv['value'])];
                unset($p['invoice']);
                db()->beginTransaction();
                $check = record('mail:'.$m['id'], true);
                if (($check['value']['lease'] ?? '') !== $lease) {
                    fail('MAIL_LEASE_LOST', 409);
                }
                $m['payload'] = $p;
                put('mail:'.$m['id'], 'mail', $m, $m['case_id']);
                db()->commit();
            }
            $send = ['from' => config()['email_from'], 'to' => [$m['recipient']], 'subject' => $p['subject'], 'text' => $p['text']];
            if (isset($p['reply_to'])) {
                $send['reply_to'] = $p['reply_to'];
            }
            if (isset($p['attachments'])) {
                $send['attachments'] = $p['attachments'];
            }
            if (isset($p['view'])) {
                try {
                    $qr = isset($p['view']['pay']) ? girocode_png($p['view']['pay'] + ['reference' => $p['view']['ref']]) : null;
                    $html = mail_html($p, $qr !== null ? 'girocode' : null);
                    if ($qr !== null) {
                        $send['attachments'][] = ['filename' => 'girocode.png', 'content' => base64_encode($qr), 'content_id' => 'girocode'];
                    }
                    $send['html'] = $html;
                } catch (Throwable) {/* the plain-text part alone still reaches the customer */
                }
            }[$code,$result] = remote_http('https://api.resend.com/emails', 'POST', $send, ['Authorization: Bearer '.config()['resend_key'], 'Content-Type: application/json', 'Idempotency-Key: rb-php/'.$m['id']]);
            $status = $code >= 200 && $code < 300 && !empty($result['id']) ? 'accepted' : ($code === 429 || $code >= 500 ? 'retry' : 'failed');
            $error = $status === 'accepted' ? null : 'PROVIDER_HTTP_'.$code;
            $provider = $result['id'] ?? null;
        } catch (Throwable) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $status = 'retry';
            $error = 'DELIVERY_UNCERTAIN';
            $provider = null;
        }
        db()->beginTransaction();
        $check = record('mail:'.$m['id'], true);
        if ($check && ($check['value']['lease'] ?? '') === $lease) {
            $m = $check['value'];
            $m['status'] = $status;
            $m['error'] = $error;
            if ($status === 'accepted' && isset($m['payload']['text'])) {
                $m['payload']['text'] = preg_replace('~(/case-access\?ref=[^#\s]*)#[a-f0-9]{64}~', '$1', $m['payload']['text']);
                $m['payload']['text'] = preg_replace('~(/admin/password\?t=)[a-f0-9]{64}~', '$1…', $m['payload']['text']);
                if (isset($m['payload']['view']['link'])) {
                    $m['payload']['view']['link'] = preg_replace('~#[a-f0-9]{64}$~', '', $m['payload']['view']['link']);
                }
            }
            $m['provider_id'] = $provider;
            $m['next_attempt_at'] = $t + min(3600, 60 * (2 ** $m['attempts']));
            unset($m['lease'],$m['lease_until']);
            put('mail:'.$m['id'], 'mail', $m, $m['case_id']);
        }
        db()->commit();
        $processed++;
    }
    return ['enabled' => true, 'processed' => $processed];
}

function refresh_delivery(string $id): array
{
    $r = record('mail:'.$id, true);
    if (!$r) {
        fail('DOCUMENT_NOT_FOUND', 404);
    }
    $m = $r['value'];
    if ($m['provider_id']) {
        [$code,$status] = remote_http('https://api.resend.com/emails/'.rawurlencode($m['provider_id']), 'GET', null, ['Authorization: Bearer '.config()['resend_key']]);
        if ($code === 200 && in_array($status['last_event'] ?? '', ['delivered', 'bounced', 'complained'], true)) {
            $m['status'] = $status['last_event'];
            if ($m['status'] === 'delivered') {
                $m['delivered_at'] = now();
            }
            put('mail:'.$id, 'mail', $m, $m['case_id']);
        }
    }
    return ['mail' => public_mail($m)];
}

function public_mail(array $m): array
{
    unset($m['payload'],$m['lease'],$m['lease_until']);
    return $m;
}
