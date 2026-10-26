<?php

// Two-factor login, required for every admin: 6-digit codes from an authenticator app (TOTP, RFC 6238) plus 10 one-time recovery codes.
// Stored as rb_records 'mfa:<email>'; the app secret is encrypted with a key derived from app_secret.
const MFA_RECOVERY_CODES = 10;
function b32_encode(string $b): string
{
    $a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($b) as $c) {
        $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    }
    $o = '';
    foreach (str_split($bits, 5) as $chunk) {
        $o .= $a[bindec(str_pad($chunk, 5, '0'))];
    }
    return $o;
}

function b32_decode(string $s): string
{
    $a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split(strtoupper($s)) as $c) {
        $i = strpos($a, $c);
        if ($i === false) {
            fail('INVALID_FIELD');
        }
        $bits .= str_pad(decbin($i), 5, '0', STR_PAD_LEFT);
    }
    $o = '';
    foreach (str_split($bits, 8) as $byte) {
        if (strlen($byte) === 8) {
            $o .= chr(bindec($byte));
        }
    }
    return $o;
}

function totp(string $key, int $step): string
{
    $h = hash_hmac('sha1', pack('J', $step), $key, true);
    $o = ord($h[19]) & 15;
    return str_pad((string)((unpack('N', substr($h, $o, 4))[1] & 0x7fffffff) % 1000000), 6, '0', STR_PAD_LEFT);
}
// The time step the code belongs to (one step either side for clock drift), only if newer than $after so a code works once.
function totp_match(string $key, string $code, int $after = 0): ?int
{
    $code = preg_replace('/\s/', '', $code);
    if (!preg_match('/^\d{6}$/', $code)) {
        return null;
    }
    $now = intdiv(time(), 30);
    for ($s = $now - 1;$s <= $now + 1;$s++) {
        if ($s > $after && hash_equals(totp($key, $s), $code)) {
            return $s;
        }
    }
    return null;
}

function mfa_key(): string
{
    return hash('sha256', 'mfa|'.config()['app_secret'], true);
}

function mfa_seal(string $key): string
{
    $iv = random_bytes(12);
    $c = openssl_encrypt($key, 'aes-256-gcm', mfa_key(), OPENSSL_RAW_DATA, $iv, $tag);
    return base64_encode($iv.$tag.$c);
}

function mfa_open(string $v): string
{
    $r = base64_decode($v, true);
    $p = $r === false ? false : openssl_decrypt(substr($r, 28), 'aes-256-gcm', mfa_key(), OPENSSL_RAW_DATA, substr($r, 0, 12), substr($r, 12, 16));
    if ($p === false) {
        fail('MFA_SECRET_UNREADABLE', 500);
    }
    return $p;
}

function mfa(string $email, bool $lock = false): ?array
{
    return record('mfa:'.$email, $lock)['value'] ?? null;
}

function mfa_code_hash(string $code): string
{
    return secret_hash('mfa-recovery|'.strtolower(preg_replace('/[^a-z0-9]/i', '', $code)));
}

function mfa_new_codes(): array
{
    $a = 'abcdefghjkmnpqrstuvwxyz23456789';
    $out = [];
    for ($i = 0;$i < MFA_RECOVERY_CODES;$i++) {
        $c = '';
        for ($j = 0;$j < 8;$j++) {
            $c .= $a[random_int(0, strlen($a) - 1)];
        }
        $out[] = substr($c, 0, 4).'-'.substr($c, 4);
    }
    return $out;
}
// Saves a freshly confirmed app secret with new recovery codes; returns the codes in plain text (shown once).
function mfa_save(string $email, string $key, int $step): array
{
    $codes = mfa_new_codes();
    put('mfa:'.$email, 'mfa', ['email' => $email, 'secret' => mfa_seal($key), 'lastStep' => $step, 'enabledAt' => now(), 'recovery' => array_map('mfa_code_hash', $codes)], $email === config()['admin_email'] ? '' : 'staff:'.$email);
    return $codes;
}

function mfa_renew_codes(string $email): array
{
    $r = mfa($email, true);
    $codes = mfa_new_codes();
    $r['recovery'] = array_map('mfa_code_hash', $codes);
    put('mfa:'.$email, 'mfa', $r, $email === config()['admin_email'] ? '' : 'staff:'.$email);
    return $codes;
}
// Checks an app code or a recovery code (which is then used up). Call inside a transaction. Returns 'app', 'recovery' or null.
function mfa_check(string $email, string $input): ?string
{
    $r = mfa($email, true);
    if (!$r) {
        return null;
    }
    $parent = $email === config()['admin_email'] ? '' : 'staff:'.$email;
    if (($s = totp_match(mfa_open($r['secret']), $input, (int)($r['lastStep'] ?? 0))) !== null) {
        $r['lastStep'] = $s;
        $r['lastUsedAt'] = now();
        put('mfa:'.$email, 'mfa', $r, $parent);
        return 'app';
    }
    $h = mfa_code_hash($input);
    foreach ($r['recovery'] as $i => $saved) {
        if (strlen(preg_replace('/[^a-z0-9]/i', '', $input)) === 8 && hash_equals($saved, $h)) {
            array_splice($r['recovery'], $i, 1);
            $r['lastUsedAt'] = now();
            put('mfa:'.$email, 'mfa', $r, $parent);
            return 'recovery';
        }
    }
    return null;
}
// Password accepted: the admin session starts only after the second step on /admin/2fa.
function mfa_begin(string $email, string $hash): never
{
    session_regenerate_id(true);
    unset($_SESSION['admin_until'],$_SESSION['admin_email'],$_SESSION['admin_fingerprint'],$_SESSION['admin_mfa']);
    $_SESSION['mfa_pending'] = ['email' => $email, 'fp' => secret_hash($hash), 'until' => time() + 900];
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    header('Location: /admin/2fa', true, 303);
    exit;
}

function mfa_finish(string $email, string $fp): void
{
    session_regenerate_id(true);
    unset($_SESSION['mfa_pending']);
    $_SESSION['admin_until'] = time() + 28800;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_fingerprint'] = $fp;
    $_SESSION['admin_mfa'] = true;
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function mfa_pending(): ?array
{
    $p = $_SESSION['mfa_pending'] ?? null;
    if (!is_array($p) || $p['until'] < time()) {
        return null;
    }
    $h = staff_hash($p['email']);
    return $h !== null && hash_equals(secret_hash($h), $p['fp']) ? $p : null;
}

function mfa_qr_svg(string $text): string
{
    $m = qr_matrix($text);
    $q = 4;
    $w = count($m) + 2 * $q;
    $path = '';
    foreach ($m as $y => $row) {
        foreach ($row as $x => $dark) {
            if ($dark) {
                $path .= 'M'.($x + $q).' '.($y + $q).'h1v1h-1z';
            }
        }
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$w.' '.$w.'" width="220" height="220" shape-rendering="crispEdges" role="img" aria-label="QR kodas"><rect width="'.$w.'" height="'.$w.'" fill="#fff"/><path fill="#000" d="'.$path.'"/></svg>';
}

function mfa_codes_html(array $codes): string
{
    $txt = "Atsarginiai prisijungimo kodai (".parse_url(config()['public_url'], PHP_URL_HOST).")\nKiekvienas kodas tinka vieną kartą.\n\n".implode("\n", $codes)."\n";
    return '<style>.mfa-codes{display:grid;grid-template-columns:repeat(2,1fr);gap:8px 20px;margin:16px 0;padding:16px 16px 16px 36px;background:#f7f4ee;border-radius:12px;font-size:14px}.mfa-codes code{font-size:16px;letter-spacing:1px}.mfa-download{font-weight:600;color:#0f6e62}</style><ol class="mfa-codes">'.implode('', array_map(fn ($c) => '<li><code>'.h($c).'</code></li>', $codes)).'</ol><p><a class="mfa-download" download="atsarginiai-kodai.txt" href="data:text/plain;charset=utf-8,'.rawurlencode($txt).'">Atsisiųsti kodus (.txt)</a></p>';
}

function mfa_error(Throwable $e): string
{
    return $e instanceof AppError && $e->reason === 'RATE_LIMITED' ? 'Per daug bandymų. Palaukite 15 minučių.' : staff_error($e);
}

function mfa_route(string $path, string $method): bool
{
    if ($path !== '/admin/2fa') {
        return false;
    }
    // Right after setup: show the recovery codes once.
    if (admin() && isset($_SESSION['mfa_new_codes'])) {
        $codes = $_SESSION['mfa_new_codes'];
        unset($_SESSION['mfa_new_codes']);
        staff_page('Atsarginiai kodai', '<p class="ok">Dviejų žingsnių prisijungimas įjungtas.</p><p>Išsisaugokite šiuos kodus saugioje vietoje (pvz. slaptažodžių programoje arba atsispausdinkite). Jei pamestumėte telefoną, prisijungsite su vienu iš jų. Kiekvienas kodas tinka vieną kartą. Daugiau jų nerodysime.</p>'.mfa_codes_html($codes).'<p><a class="mfa-button" href="/admin">Išsisaugojau kodus, tęsti</a></p>');
    }
    if (admin()) {
        header('Location: /admin', true, 303);
        exit;
    }
    $p = mfa_pending();
    if (!$p) {
        header('Location: /admin/login', true, 303);
        exit;
    }
    $email = $p['email'];
    $error = '';
    $has = mfa($email) !== null;
    if ($method === 'POST') {
        try {
            csrf();
            $code = is_string($_POST['code'] ?? null) ? substr($_POST['code'], 0, 40) : '';
            db()->beginTransaction();
            rate('admin-mfa:'.$email, 10, 900);
            rate('admin-mfa-ip:'.($_SERVER['REMOTE_ADDR'] ?? ''), 30, 900);
            db()->commit();
            db()->beginTransaction();
            if ($has) {
                $ok = mfa_check($email, $code);
                db()->commit();
                if ($ok) {
                    mfa_finish($email, $p['fp']);
                    if ($ok === 'recovery') {
                        $_SESSION['mfa_used_recovery'] = true;
                    }
                    header('Location: /admin', true, 303);
                    exit;
                }
                $error = 'Kodas neteisingas arba jau panaudotas. Įveskite naują kodą iš programėlės.';
            } else {
                $key = b32_decode($p['secret'] ?? '');
                $step = $key !== '' ? totp_match($key, $code) : null;
                if ($step === null) {
                    db()->rollBack();
                    $error = 'Kodas neteisingas. Patikrinkite, ar telefono laikas nustatytas automatiškai, ir įveskite naują kodą.';
                } elseif (mfa($email, true)) {
                    db()->rollBack();
                    $error = '2FA jau įjungtas. Įveskite kodą iš programėlės.';
                    $has = true;
                } else {
                    $codes = mfa_save($email, $key, $step);
                    db()->commit();
                    mfa_finish($email, $p['fp']);
                    $_SESSION['mfa_new_codes'] = $codes;
                    header('Location: /admin/2fa', true, 303);
                    exit;
                }
            }
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $error = mfa_error($e);
        }
    }
    $err = $error ? '<p class="error" role="alert">'.h($error).'</p>' : '';
    $csrf = '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">';
    $back = '<p class="note"><a href="/admin/login">Prisijungti kitu vardu</a></p>';
    if ($has) {
        staff_page('Patvirtinimo kodas', $err.'<p>Atidarykite autentifikavimo programėlę telefone ir įveskite 6 skaitmenų kodą, rodomą prie <strong>'.h(parse_url(config()['public_url'], PHP_URL_HOST)).'</strong> ('.h($email).').</p><form method="post">'.$csrf.'<label>Kodas<input name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,7}|[A-Za-z0-9]{4}-?[A-Za-z0-9]{4}" maxlength="9" required autofocus></label><button>Patvirtinti</button></form><details class="mfa-help"><summary>Neturite telefono?</summary><p>Įveskite vieną iš atsarginių kodų, gautų įjungiant 2FA (pvz. abcd-2345). Jei jų neturite, paprašykite pagrindinio administratoriaus atstatyti 2FA skiltyje „Komanda“.</p></details>'.$back);
    }
    if (empty($p['secret'])) {
        $p['secret'] = b32_encode(random_bytes(20));
        $_SESSION['mfa_pending'] = $p;
    }
    $host = parse_url(config()['public_url'], PHP_URL_HOST);
    $uri = 'otpauth://totp/'.rawurlencode($host).':'.rawurlencode($email).'?secret='.$p['secret'].'&issuer='.rawurlencode($host).'&algorithm=SHA1&digits=6&period=30';
    staff_page('Įjunkite 2FA', $err.'<p>Administravime dabar privalomas dviejų žingsnių prisijungimas. Tai užtruks apie minutę.</p>'
    .'<ol class="mfa-steps"><li>Telefone įsidiekite nemokamą autentifikavimo programėlę, pvz. <strong>Google Authenticator</strong> arba <strong>Microsoft Authenticator</strong>.</li><li>Programėlėje pasirinkite „+“ ir nuskenuokite šį QR kodą.<div class="mfa-qr">'.mfa_qr_svg($uri).'</div><details class="mfa-help"><summary>Negalite nuskenuoti?</summary><p>Įveskite raktą ranka (laiko pagrindu):</p><p class="mfa-secret"><code>'.h(trim(chunk_split($p['secret'], 4, ' '))).'</code></p></details></li><li>Įveskite 6 skaitmenų kodą, kurį rodo programėlė.</li></ol>'
    .'<form method="post">'.$csrf.'<label>Kodas iš programėlės<input name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,7}" maxlength="7" required></label><button>Įjungti ir prisijungti</button></form>'.$back);
}
