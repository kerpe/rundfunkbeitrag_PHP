<?php

// Card payment through Stripe Checkout, next to the bank transfer. Keys and the switch live in config.php (admin "Sistema").
// stripe_mode: 'off' (default), 'test' (only orders with stripe_test_email see the button), 'on' (everyone).
// A payment is recorded like the staff "payment" action: by the return page and by the webhook, whichever comes first.

function stripe_config(): array
{
    $c = config();
    return ['mode' => in_array($c['stripe_mode'] ?? 'off', ['off', 'test', 'on'], true) ? ($c['stripe_mode'] ?? 'off') : 'off', 'key' => (string)($c['stripe_secret_key'] ?? ''), 'whsec' => (string)($c['stripe_webhook_secret'] ?? ''), 'testEmail' => strtolower(trim((string)($c['stripe_test_email'] ?? '')))];
}

// Card payment is open to every customer (texts on the homepage, FAQ and service pages then mention it).
function stripe_public(): bool
{
    $s = stripe_config();
    return $s['mode'] === 'on' && $s['key'] !== '';
}

function stripe_offered(array $c): bool
{
    $s = stripe_config();
    if ($s['mode'] === 'off' || $s['key'] === '' || empty($c['order']) || $c['state'] === 'draft' || !empty($c['checks']['paid']) || isset($c['operations']['withdrawal'])) {
        return false;
    }
    return $s['mode'] === 'on' || ($s['testEmail'] !== '' && strtolower(trim($c['data']['email'] ?? '')) === $s['testEmail']);
}

function stripe_api(string $method, string $path, array $params = []): array
{
    $ch = curl_init('https://api.stripe.com/v1/'.$path.($method === 'GET' && $params ? '?'.http_build_query($params) : ''));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_USERPWD => stripe_config()['key'].':', CURLOPT_HTTPHEADER => ['Stripe-Version: 2024-06-20']]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $out = is_string($raw) ? (json_decode($raw, true) ?? []) : [];
    if ($raw === false || $code >= 300) {
        error_log('Stripe '.$path.' '.$code.' '.($out['error']['message'] ?? 'no response'));
        fail('PAYMENT_PROVIDER_UNAVAILABLE', 502);
    }
    return $out;
}

// One open Checkout session per order is reused until it is about to expire, so a double click does not create two.
function stripe_checkout_url(array $c): string
{
    $saved = record('stripe:'.$c['id'])['value'] ?? null;
    if ($saved && ($saved['expires'] ?? 0) > time() + 600 && ($saved['key'] ?? '') === substr(hash('sha256', stripe_config()['key']), 0, 16)) {
        return $saved['url'];
    }
    $o = $c['order'];
    $l = rb_lang($o['language'] ?? 'de');
    $base = config()['public_url'];
    $s = stripe_api('POST', 'checkout/sessions', [
        'mode' => 'payment',
        'line_items' => [['quantity' => 1, 'price_data' => ['currency' => 'eur', 'unit_amount' => (int)($o['feeCents'] ?? 4900), 'product_data' => ['name' => pick_lang(rules()['routes'][$c['route']]['title'], $l).' · '.$o['reference']]]]],
        'customer_email' => strtolower(trim($c['data']['email'])),
        'client_reference_id' => $c['id'],
        'metadata' => ['case_id' => $c['id'], 'reference' => $o['reference']],
        'payment_intent_data' => ['description' => $o['reference'], 'metadata' => ['case_id' => $c['id'], 'reference' => $o['reference']]],
        'locale' => $l === 'en' ? 'en' : 'de',
        'success_url' => $base.'/zahlung/fertig?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $base.'/antrag?case='.rawurlencode($c['id']),
    ]);
    put('stripe:'.$c['id'], 'stripe', ['session' => $s['id'], 'url' => $s['url'], 'expires' => (int)$s['expires_at'], 'key' => substr(hash('sha256', stripe_config()['key']), 0, 16)], $c['id'], '', (int)$s['expires_at']);
    return $s['url'];
}

// Records a paid Checkout session on its order once; the "payment received" email and the invoice follow as with staff.
// Test-mode payments get no invoice number and no Google Ads conversion.
function stripe_record(array $s): ?string
{
    $id = (string)($s['metadata']['case_id'] ?? '');
    if (($s['payment_status'] ?? '') !== 'paid' || !preg_match('/^[a-f0-9-]{36}$/i', $id)) {
        return null;
    }
    $r = record('case:'.$id, true);
    if (!$r || $r['owner_hash'] === 'erasing' || empty($r['value']['order'])) {
        return null;
    }
    $c = $r['value'];
    if (!empty($c['checks']['paid'])) {
        return $id;
    }
    if ((int)($s['amount_total'] ?? 0) !== (int)($c['order']['feeCents'] ?? 4900) || strtolower((string)($s['currency'] ?? '')) !== 'eur') {
        error_log('Stripe amount mismatch for case '.$id.': '.($s['amount_total'] ?? '?').' '.($s['currency'] ?? '?'));
        return $id;
    }
    $live = !empty($s['livemode']);
    $h = hash('sha256', 'stripe:'.$s['id']);
    $op = substr($h, 0, 8).'-'.substr($h, 8, 4).'-'.substr($h, 12, 4).'-'.substr($h, 16, 4).'-'.substr($h, 20, 12);
    if (record('event:'.$op)) {
        return $id;
    }
    $n = $c;
    $n['operations'] ??= [];
    $n['checks']['paid'] = true;
    $n['checks']['paymentReference'] = 'Stripe'.($live ? '' : ' TEST').' · '.($s['payment_intent'] ?? $s['id']);
    $n['checks']['paidAt'] = now();
    if (empty($n['operations']['paymentNotified']) && !record('invoice:'.$c['id'])) {
        $n['operations']['paymentNotified'] = now();
    }
    if ($live) {
        issue_invoice($n, invoice_service_date($n), billing_address($n));
    }
    save_case($c, $n, $op, 'stripe', 'payment', ['session' => $s['id']]);
    return $id;
}

function stripe_webhook(): never
{
    $raw = (string)file_get_contents('php://input');
    $secret = stripe_config()['whsec'];
    $t = 0;
    $sigs = [];
    foreach (explode(',', (string)($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '')) as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($k === 't') {
            $t = (int)$v;
        } elseif ($k === 'v1') {
            $sigs[] = $v;
        }
    }
    $expected = hash_hmac('sha256', $t.'.'.$raw, $secret);
    $ok = $secret !== '' && abs(time() - $t) <= 300 && array_filter($sigs, fn ($s) => hash_equals($expected, $s));
    if (!$ok) {
        response_json(['error' => 'INVALID_SIGNATURE'], 400);
    }
    $e = json_decode($raw, true) ?? [];
    if (in_array($e['type'] ?? '', ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
        db()->beginTransaction();
        stripe_record($e['data']['object'] ?? []);
        db()->commit();
        dispatch_mail(1);
    }
    response_json(['received' => true]);
}

function stripe_route(string $path, string $method): void
{
    if ($path === '/api/stripe/webhook') {
        if ($method !== 'POST') {
            fail('METHOD_NOT_ALLOWED', 405);
        }
        stripe_webhook();
    }
    if ($path === '/zahlung/karte') {
        $id = (string)($_GET['case'] ?? '');
        try {
            $c = current_case(preg_match('/^[a-f0-9-]{36}$/i', $id) ? $id : '-');
            if (!stripe_offered($c)) {
                header('Location: /antrag?case='.rawurlencode($id), true, 303);
                exit;
            }
            rate('stripe:'.$c['id'], 20, 3600);
            $url = stripe_checkout_url($c);
        } catch (AppError $e) {
            http_response_code($e->reason === 'CASE_NOT_FOUND' ? 404 : 503);
            echo form_html('Zahlung / Payment', '<p>Die Kartenzahlung ist gerade nicht möglich. Bitte versuchen Sie es später erneut oder zahlen Sie per Überweisung.</p><p>Card payment is not available right now. Please try again later or pay by bank transfer.</p><p><a href="/antrag?case='.h(rawurlencode($id)).'">Zurück zu Ihrem Vorgang / Back to your case</a></p>');
            exit;
        }
        header('Location: '.$url, true, 303);
        exit;
    }
    if ($path === '/zahlung/fertig') {
        $sid = (string)($_GET['session_id'] ?? '');
        $id = '';
        if (preg_match('/^cs_[A-Za-z0-9_]{10,200}$/', $sid) && stripe_config()['key'] !== '') {
            try {
                $s = stripe_api('GET', 'checkout/sessions/'.$sid);
                db()->beginTransaction();
                $id = stripe_record($s) ?? (string)($s['metadata']['case_id'] ?? '');
                db()->commit();
                dispatch_mail(1);
            } catch (Throwable $e) {
                if (db()->inTransaction()) {
                    db()->rollBack();
                }
                error_log('Stripe return: '.$e->getMessage());
            }
        }
        header('Location: '.(preg_match('/^[a-f0-9-]{36}$/i', $id) ? '/antrag?case='.rawurlencode($id) : '/case-access'), true, 303);
        exit;
    }
}
