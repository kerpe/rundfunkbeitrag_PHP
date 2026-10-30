<?php

require __DIR__.'/staff.php';
require __DIR__.'/mfa.php';
function form_html(string $title, string $content, string $lang = 'de'): string
{
    return '<!doctype html><html lang="'.$lang.'"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>'.h($title).' · Rundfunkbeitrag Support</title><link rel="stylesheet" href="/admin-login.css?v='.@filemtime(dirname(private_root()).'/public_html/admin-login.css').'"><link rel="icon" href="/favicon.svg"></head><body><main><a class="brand" href="/"><img src="/brand/antenna.svg" width="48" height="48" alt=""><span>Rundfunkbeitrag<strong>Support <i class="black"></i><i class="red"></i><i class="gold"></i></strong></span></a><h1>'.h($title).'</h1>'.$content.''.($lang === 'de' ? '<footer>Wir sind nicht mit ARD, ZDF, Deutschlandradio oder Beitragsservice verbunden.</footer>' : '').'</main></body></html>';
}

function login_page(string $error = ''): never
{
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: private, no-store');
    echo form_html('Administrator', ($error ? '<p class="error" role="alert">'.h($error).'</p>' : '').'<form method="post" action="/api/admin/login"><input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'"><label>E-Mail<input name="email" type="email" autocomplete="username" required></label><label>Passwort<input name="password" type="password" autocomplete="current-password" required maxlength="256"></label><button>Anmelden</button></form>');
    exit;
}

function auth_route(string $path, string $method): bool
{
    if (staff_route($path, $method) || mfa_route($path, $method)) {
        return true;
    }
    if ($path === '/admin/settings-transfer') {
        // Copy the start settings between two installations (e.g. Hostinger -> OVH): export here, paste on the other site. Real orders stay off after an import; the Resend key is never shown.
        if (!admin()) {
            header('Location: /admin/login', true, 303);
            exit;
        }
        $error = '';
        $done = '';
        if ($method === 'POST') {
            try {
                csrf();
                $in = json_decode((string)($_POST['data'] ?? ''), true);
                if (!is_array($in) || !is_array($in['settings'] ?? null)) {
                    fail('INVALID_TRANSFER_DATA');
                }
                $s = record('settings:service', true)['value'] ?? rules()['emptySettings'];
                $next = rules()['emptySettings'];
                foreach ($next as $key => $v) {
                    if (!in_array($key, ['version', 'liveEnabled'], true)) {
                        $next[$key] = text((string)($in['settings'][$key] ?? ''), 0, settings_max($key));
                    }
                }
                $next['iban'] = strtoupper(preg_replace('/\s/', '', $next['iban']));
                $next['version'] = $s['version'] + 1;
                $next['liveEnabled'] = false;
                put('settings:service', 'settings', $next);
                $from = text((string)($in['email_from'] ?? ''), 0, 250);
                $address = preg_match('/<([^<>]+)>$/', $from, $m) ? $m[1] : $from;
                if ($from !== '' && !preg_match('/[\r\n]/', $from) && filter_var($address, FILTER_VALIDATE_EMAIL) && empty(config()['email_from'])) {
                    $c = config();
                    $c['email_from'] = $from;
                    $tmp = tempnam(private_root(), '.config-');
                    chmod($tmp, 0600);
                    file_put_contents($tmp, "<?php\nreturn ".var_export($c, true).";\n", LOCK_EX);
                    if (!rename($tmp, private_root().'/config.php')) {
                        fail('CONFIG_WRITE_FAILED', 503);
                    }
                    if (function_exists('opcache_invalidate')) {
                        opcache_invalidate(private_root().'/config.php', true);
                    }
                }
                $done = 'Übernommen. Echte Bestellungen bleiben ausgeschaltet. / Imported. Real orders stay switched off.';
            } catch (AppError $e) {
                $error = $e->reason;
            }
        }
        $s = settings();
        unset($s['version'],$s['liveEnabled']);
        $export = json_encode(['settings' => $s, 'email_from' => config()['email_from'] ?? ''], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: private, no-store');
        echo form_html('Einstellungen übertragen', ($error ? '<p class="error">'.h($error).'</p>' : '').($done ? '<p>'.h($done).'</p>' : '').'<p>Export dieser Website (kopieren und auf der anderen Website einfügen). / Export of this site.</p><label><textarea readonly rows="12" onclick="this.select()">'.h($export).'</textarea></label><form method="post"><input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'"><label>Import (überschreibt die Starteinstellungen dieser Website) / Import<textarea name="data" rows="8" required></textarea></label><button>Importieren / Import</button></form><p><a href="/admin">Zum Verwaltungsbereich / Back to administration</a></p>');
        exit;
    }
    if ($path === '/admin/configuration') {
        if (!admin()) {
            header('Location: /admin/login', true, 303);
            exit;
        }
        $c = config();
        $error = '';
        if ($method === 'POST') {
            try {
                csrf();
                $key = text($_POST['resend_key'] ?? '', 0, 256);
                if ($key !== '' && !preg_match('/^re_[A-Za-z0-9_-]+$/', $key)) {
                    fail('INVALID_EMAIL_KEY');
                }
                $from = text($_POST['email_from'] ?? '', 0, 250);
                if (preg_match('/[\r\n]/', $from)) {
                    fail('INVALID_EMAIL');
                }
                $address = preg_match('/<([^<>]+)>$/', $from, $m) ? $m[1] : $from;
                if ($from !== '' && !filter_var($address, FILTER_VALIDATE_EMAIL)) {
                    fail('INVALID_EMAIL');
                }
                if ($key !== '') {
                    $c['resend_key'] = $key;
                }
                $c['email_from'] = $from;
                $c['indexing'] = isset($_POST['indexing']);
                $ga = strtoupper(trim(text($_POST['ga4_id'] ?? '', 0, 30)));
                if ($ga !== '' && !preg_match('/^G-[A-Z0-9]{4,20}$/', $ga)) {
                    fail('INVALID_GA4_ID');
                }
                $c['ga4_id'] = $ga;
                $sk = trim(text($_POST['stripe_secret_key'] ?? '', 0, 256));
                if ($sk !== '' && !preg_match('/^(sk|rk)_(test|live)_[A-Za-z0-9]+$/', $sk)) {
                    fail('INVALID_STRIPE_KEY');
                }
                $wh = trim(text($_POST['stripe_webhook_secret'] ?? '', 0, 256));
                if ($wh !== '' && !preg_match('/^whsec_[A-Za-z0-9]+$/', $wh)) {
                    fail('INVALID_STRIPE_WEBHOOK');
                }
                if ($sk !== '') {
                    $c['stripe_secret_key'] = $sk;
                }
                if ($wh !== '') {
                    $c['stripe_webhook_secret'] = $wh;
                }
                $te = strtolower(trim(text($_POST['stripe_test_email'] ?? '', 0, 250)));
                if ($te !== '' && !filter_var($te, FILTER_VALIDATE_EMAIL)) {
                    fail('INVALID_EMAIL');
                }
                $c['stripe_test_email'] = $te;
                $c['stripe_mode'] = in_array($_POST['stripe_mode'] ?? '', ['off', 'test', 'on'], true) ? $_POST['stripe_mode'] : 'off';
                if ($c['stripe_mode'] !== 'off' && (empty($c['stripe_secret_key']) || empty($c['stripe_webhook_secret']))) {
                    fail('STRIPE_KEYS_MISSING');
                }
                if ($c['stripe_mode'] === 'test' && $te === '') {
                    fail('STRIPE_TEST_EMAIL_MISSING');
                }
                $tmp = tempnam(private_root(), '.config-');
                chmod($tmp, 0600);
                file_put_contents($tmp, "<?php\nreturn ".var_export($c, true).";\n", LOCK_EX);
                if (!rename($tmp, private_root().'/config.php')) {
                    fail('CONFIG_WRITE_FAILED', 503);
                }
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate(private_root().'/config.php', true);
                }
                header('Location: /admin/configuration?saved=1', true, 303);
                exit;
            } catch (AppError $e) {
                $error = ['INVALID_EMAIL_KEY' => 'Resend raktas turi prasidėti „re_“.', 'INVALID_EMAIL' => 'Neteisingas siuntėjo adresas.', 'CONFIG_WRITE_FAILED' => 'Nepavyko įrašyti nustatymų failo serveryje.', 'INVALID_GA4_ID' => 'Google Analytics ID turi atrodyti taip: G-XXXXXXXXXX.', 'INVALID_STRIPE_KEY' => 'Stripe slaptasis raktas turi prasidėti „sk_test_“, „sk_live_“, „rk_test_“ arba „rk_live_“.', 'INVALID_STRIPE_WEBHOOK' => 'Stripe pranešimų (webhook) raktas turi prasidėti „whsec_“.', 'STRIPE_KEYS_MISSING' => 'Kad įjungtumėte mokėjimus kortele, įrašykite abu Stripe raktus.', 'STRIPE_TEST_EMAIL_MISSING' => 'Bandymo režimui įrašykite el. pašto adresą, kurio užsakymuose bus rodomas mygtukas.', 'CSRF_REQUIRED' => 'Puslapis paseno. Atnaujinkite jį ir bandykite dar kartą.', 'SAME_ORIGIN_REQUIRED' => 'Puslapis paseno. Atnaujinkite jį ir bandykite dar kartą.'][$e->reason] ?? $e->reason;
            }
        }
        $mail = !empty($c['resend_key']);
        $sm = in_array($c['stripe_mode'] ?? 'off', ['test', 'on'], true) ? $c['stripe_mode'] : 'off';
        $sk = (string)($c['stripe_secret_key'] ?? '');
        $skNote = $sk === '' ? 'sk_live_… arba sk_test_…' : 'Išsaugotas '.(str_contains($sk, '_test_') ? 'BANDOMASIS' : 'TIKRASIS').' raktas (…'.substr($sk, -4).'). Palikite tuščią, kad nekeistumėte.';
        $opt = fn (string $v, string $label) => '<label class="rb-adm-check"><input type="radio" name="stripe_mode" value="'.$v.'" '.($sm === $v ? 'checked' : '').'><span>'.$label.'</span></label>';
        admin_page('system', 'Sistema', admin_msg($error, isset($_GET['saved']) && !$error ? 'Išsaugota.' : '').'<form method="post">'.admin_csrf()
        .'<section class="mj-card rb-adm-card"><div class="rb-adm-card-head"><h2>Laiškų siuntimas</h2><span class="rb-adm-pill '.($mail ? 'rb-adm-pill-ok">Veikia' : 'rb-adm-pill-wait">Neįjungta').'</span></div><p class="rb-adm-muted">Visi laiškai klientams ir jums siunčiami per Resend.</p><div class="rb-adm-grid2"><label class="rb-adm-f">Resend API raktas<input type="password" name="resend_key" autocomplete="new-password" placeholder="'.($mail ? 'Raktas išsaugotas. Palikite tuščią, kad nekeistumėte.' : 're_…').'"><small>Raktą rasite resend.com → API Keys.</small></label><label class="rb-adm-f">Siuntėjas<input name="email_from" value="'.h($c['email_from']).'" maxlength="250"><small>Pvz. Rundfunkbeitrag Support &lt;info@rundfunkbeitragsupport.de&gt;. Domenas turi būti patvirtintas Resend.</small></label></div></section>'
        .'<section class="mj-card rb-adm-card"><div class="rb-adm-card-head"><h2>Paieškos sistemos</h2><span class="rb-adm-pill '.($c['indexing'] ? 'rb-adm-pill-ok">Leidžiama' : 'rb-adm-pill-wait">Išjungta').'</span></div><label class="rb-adm-check"><input type="checkbox" name="indexing" '.($c['indexing'] ? 'checked' : '').'><span><strong>Leisti Google rodyti svetainę paieškoje</strong><br><span class="rb-adm-muted">Įjunkite tik vienoje svetainėje, kad Google nematytų dviejų vienodų.</span></span></label></section>'
        .'<section class="mj-card rb-adm-card" id="analytics"><div class="rb-adm-card-head"><h2>Google Analytics</h2><span class="rb-adm-pill '.(!empty($c['ga4_id']) ? 'rb-adm-pill-ok">Įjungta' : 'rb-adm-pill-wait">Išjungta').'</span></div><p class="rb-adm-muted">Lankytojų statistika. Įkeliama tik lankytojui paspaudus „Akzeptieren“ slapukų lange (kaip ir Google Ads žyma). Prieš įjungdami atnaujinkite privatumo politiką.</p><div class="rb-adm-grid2"><label class="rb-adm-f">Matavimo ID (Measurement ID)<input name="ga4_id" value="'.h($c['ga4_id'] ?? '').'" maxlength="30" placeholder="G-XXXXXXXXXX" autocomplete="off"><small>Google Analytics → Administravimas → Duomenų srautai → jūsų svetainė. Palikite tuščią, kad išjungtumėte.</small></label></div></section>'
        .'<section class="mj-card rb-adm-card" id="stripe"><div class="rb-adm-card-head"><h2>Mokėjimai kortele (Stripe)</h2><span class="rb-adm-pill '.['off' => 'rb-adm-pill-wait">Išjungta', 'test' => 'rb-adm-pill-wait">Bandymas', 'on' => 'rb-adm-pill-ok">Įjungta'][$sm].'</span></div><p class="rb-adm-muted">Klientai užsakymo puslapyje mato mygtuką „Apmokėti kortele“. Sumokėjus užsakymas pats pažymimas kaip apmokėtas, klientas gauna įprastą laišką. Banko pavedimas lieka.</p>'
        .$opt('off', '<strong>Išjungta</strong><br><span class="rb-adm-muted">Niekas nemato mokėjimo kortele.</span>').$opt('test', '<strong>Bandymas</strong><br><span class="rb-adm-muted">Mygtuką mato tik užsakymai su žemiau nurodytu el. paštu. Naudokite su bandomaisiais raktais.</span>').$opt('on', '<strong>Įjungta visiems klientams</strong><br><span class="rb-adm-muted">Naudokite su tikraisiais raktais. Tada pradžios puslapyje, DUK ir paslaugų puslapiuose minimas ir mokėjimas internetu.</span>')
        .($sm === 'on' && str_contains($sk, '_test_') ? '<p class="rb-adm-msg rb-adm-msg-err">Įjungta visiems, bet išsaugotas BANDOMASIS raktas: klientai negalės tikrai sumokėti.</p>' : '')
        .'<div class="rb-adm-grid2"><label class="rb-adm-f">Slaptasis raktas<input type="password" name="stripe_secret_key" autocomplete="new-password" placeholder="'.h($skNote).'"><small>Stripe → Developers → API keys → Secret key.</small></label><label class="rb-adm-f">Pranešimų (webhook) raktas<input type="password" name="stripe_webhook_secret" autocomplete="new-password" placeholder="'.(empty($c['stripe_webhook_secret']) ? 'whsec_…' : 'Raktas išsaugotas. Palikite tuščią, kad nekeistumėte.').'"><small>Pranešimų adresas: '.h(config()['public_url']).'/api/stripe/webhook, įvykiai checkout.session.completed ir checkout.session.async_payment_succeeded.</small></label><label class="rb-adm-f">Bandymo el. paštas<input type="email" name="stripe_test_email" value="'.h($c['stripe_test_email'] ?? '').'" maxlength="250"><small>Tik bandymo režimui: užsakymai su šiuo el. paštu matys mygtuką.</small></label></div></section>'
        .'<div class="rb-adm-form-actions"><button class="rb-btn">Išsaugoti</button></div></form>');

    }
    if ($path === '/admin/login') {
        if ($method !== 'GET') {
            fail('METHOD_NOT_ALLOWED', 405);
        }
        login_page();
    }
    if ($path === '/api/admin/login') {
        if ($method !== 'POST') {
            fail('METHOD_NOT_ALLOWED', 405);
        }
        csrf();
        db()->beginTransaction();
        rate('admin-login:'.($_SERVER['REMOTE_ADDR'] ?? ''), 15, 900);
        db()->commit();
        if (!is_string($_POST['password'] ?? null) || strlen($_POST['password']) > 72 || !is_string($_POST['email'] ?? null) || !($hash = staff_hash(strtolower(trim($_POST['email'])))) || !password_verify($_POST['password'], $hash)) {
            http_response_code(401);
            login_page('E-Mail oder Passwort ist nicht korrekt.');
        }
        mfa_begin(strtolower(trim($_POST['email'])), $hash);
    }
    if ($path === '/api/admin/logout') {
        if ($method !== 'POST') {
            fail('METHOD_NOT_ALLOWED', 405);
        }
        csrf();
        unset($_SESSION['admin_until'],$_SESSION['admin_fingerprint'],$_SESSION['admin_email']);
        session_regenerate_id(true);
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: /admin/login', true, 303);
        exit;
    }
    return false;
}

function admin_password(mixed $value): string
{
    text($value, 14, 72);
    if (strlen($value) > 72) {
        fail('PASSWORD_MAX_72_BYTES');
    }
    return $value;
}

function installer(): never
{
    if (is_file(private_root().'/config.php')) {
        http_response_code(404);
        exit;
    }
    $keyFile = private_root().'/installation-key.php';
    if (!is_file($keyFile)) {
        http_response_code(503);
        echo form_html('Installation', '<p>Prepare the private installation key using the supplied instructions.</p>');
        exit;
    }
    $secret = require $keyFile;
    if (!is_string($secret) || strlen($secret) < 24 || $secret === 'CHOOSE_AT_LEAST_24_RANDOM_CHARACTERS') {
        http_response_code(503);
        echo form_html('Installation', '<p>Set your own private installation key first.</p>');
        exit;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $local = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host);
    if (!$secure && !$local) {
        http_response_code(403);
        echo form_html('Installation', '<p>Open the installer using HTTPS.</p>');
        exit;
    }
    $dir = private_root().'/data';
    foreach (['sessions', 'uploads', 'tmp', 'fonts'] as $part) {
        if (!is_dir($dir.'/'.$part)) {
            mkdir($dir.'/'.$part, 0700, true);
        }
    }
    session_save_path($dir.'/sessions');
    session_name($secure ? '__Host-rb-session' : 'rb-session-local');
    session_set_cookie_params(['path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict']);
    ini_set('session.use_strict_mode', '1');
    session_start();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '') || ($_SERVER['HTTP_ORIGIN'] ?? '') !== ($secure ? 'https://' : 'http://').$host) {
                fail('SAME_ORIGIN_REQUIRED', 403);
            }
            $attemptFile = $dir.'/installer-attempts.json';
            $lock = fopen($dir.'/installer.lock', 'c+');
            if (!$lock || !flock($lock, LOCK_EX)) {
                fail('INSTALLATION_BUSY', 503);
            }
            if (is_file(private_root().'/config.php')) {
                fail('ALREADY_INSTALLED', 409);
            }
            $attempts = is_file($attemptFile) ? json_decode(file_get_contents($attemptFile), true) : ['count' => 0, 'until' => time() + 900];
            if ($attempts['until'] < time()) {
                $attempts = ['count' => 0, 'until' => time() + 900];
            }
            if (++$attempts['count'] > 15) {
                fail('RATE_LIMITED', 429);
            }
            file_put_contents($attemptFile, json_encode($attempts), LOCK_EX);
            chmod($attemptFile, 0600);
            if (!hash_equals($secret, $_POST['installation_key'] ?? '')) {
                fail('INVALID_INSTALLATION_KEY', 403);
            }
            $url = rtrim(text($_POST['public_url'] ?? null, 8, 250), '/');
            $expected = ($secure ? 'https://' : 'http://').$host;
            if ($url !== $expected) {
                fail('PUBLIC_URL_MUST_MATCH_THIS_SITE');
            }
            $dbHost = text($_POST['db_host'] ?? 'localhost');
            $dbName = text($_POST['db_name'] ?? null);
            if (!preg_match('/^[A-Za-z0-9._-]+$/', $dbHost) || !preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
                fail('INVALID_DATABASE_DETAILS');
            }
            $c = ['public_url' => $url, 'db_dsn' => 'mysql:host='.$dbHost.';dbname='.$dbName.';charset=utf8mb4', 'db_user' => text($_POST['db_user'] ?? null, 1, 150), 'db_password' => text($_POST['db_password'] ?? null, 1, 256), 'admin_email' => email($_POST['admin_email'] ?? null), 'admin_hash' => password_hash(admin_password($_POST['admin_password'] ?? null), PASSWORD_DEFAULT), 'app_secret' => bin2hex(random_bytes(32)), 'resend_key' => text($_POST['resend_key'] ?? '', 0, 256), 'email_from' => text($_POST['email_from'] ?? '', 0, 250), 'indexing' => false];
            $pdo = new PDO($c['db_dsn'], $c['db_user'], $c['db_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec(file_get_contents(__DIR__.'/schema.sql'));
            $stmt = $pdo->prepare('INSERT IGNORE INTO rb_records(id,kind,parent_id,owner_hash,data_json,version,created_at,updated_at) VALUES (?,?,\'\',\'\',?,1,?,?)');
            $stmt->execute(['counter:invoice', 'counter', '{"value":0}', time(), time()]);
            $stmt->execute(['settings:service', 'settings', json_encode(rules()['emptySettings'], JSON_UNESCAPED_UNICODE), time(), time()]);
            $content = "<?php\n// Private configuration. Never commit or expose this file.\nreturn ".var_export($c, true).";\n";
            $file = fopen(private_root().'/config.php', 'x');
            if (!$file) {
                fail('CONFIG_WRITE_FAILED', 503);
            }
            chmod(private_root().'/config.php', 0600);
            if (fwrite($file, $content) !== strlen($content)) {
                fclose($file);
                unlink(private_root().'/config.php');
                fail('CONFIG_WRITE_FAILED', 503);
            }
            fclose($file);
            unlink($keyFile);
            flock($lock, LOCK_UN);
            fclose($lock);
            session_regenerate_id(true);
            $_SESSION = [];
            header('Location: /admin/login', true, 303);
            exit;
        } catch (Throwable $e) {
            if (isset($lock) && is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
            $error = $e instanceof AppError ? $e->reason : 'Database or folder configuration could not be completed. Check your details.';
            error_log('Installer: '.($e instanceof AppError ? $e->reason : 'Database/setup error'));
        }
    }
    $fields = ['installation_key' => ['Diegimo raktas', 'password', ''], 'public_url' => ['Svetainės adresas', 'url', ($secure ? 'https://' : 'http://').$host], 'db_host' => ['MySQL serveris', 'text', 'localhost'], 'db_name' => ['Duomenų bazės pavadinimas', 'text', ''], 'db_user' => ['Duomenų bazės vartotojas', 'text', ''], 'db_password' => ['Duomenų bazės slaptažodis', 'password', ''], 'admin_email' => ['Administratoriaus el. paštas', 'email', ''], 'admin_password' => ['Administratoriaus slaptažodis (14+ simbolių, iki 72 baitų)', 'password', ''], 'resend_key' => ['Resend API raktas (galima papildyti vėliau)', 'password', ''], 'email_from' => ['Laiškų siuntėjas (galima papildyti vėliau)', 'text', '']];
    $html = '<p>PHP svetainės pirmasis paleidimas. MySQL duomenis rasite Hostinger skydelyje.</p>'.($error ? '<p class="error">'.h($error).'</p>' : '').'<form method="post"><input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">';
    foreach ($fields as $name => [$label,$type,$value]) {
        $html .= '<label>'.h($label).'<input name="'.$name.'" type="'.$type.'" value="'.h($value).'" '.(!in_array($name, ['resend_key', 'email_from'], true) ? 'required' : '').' autocomplete="'.($type === 'password' ? 'new-password' : 'off').'"></label>';
    }
    $html .= '<button>Paruošti svetainę</button></form>';
    echo form_html('Pirmasis paleidimas', $html);
    exit;
}
