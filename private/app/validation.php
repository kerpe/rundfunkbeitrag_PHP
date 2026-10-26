<?php

function field_set(string $route, array $d): array
{
    $mask = 0;
    foreach (($d['changes'] ?? []) as $v) {
        if ($v === 'name') {
            $mask |= 1;
        }
        if ($v === 'address') {
            $mask |= 2;
        }
    }
    $rx = $route === 'death' && ($d['remaining'] ?? '') === 'yes' ? (in_array($d['remainingWho'] ?? '', ['self', 'other'], true) ? $d['remainingWho'] : '').','.(in_array($d['remainingRegistered'] ?? '', ['yes', 'no'], true) ? $d['remainingRegistered'] : '') : ($route === 'second' && in_array($d['secondRegistered'] ?? '', ['yes', 'no'], true) ? $d['secondRegistered'] : '');
    $key = implode('|', [$route, ($d['forWhom'] ?? '') === 'other' ? 'other' : 'self', in_array($d['remaining'] ?? '', ['yes', 'no', 'unsure'], true) ? $d['remaining'] : '', $mask, in_array($d['postTo'] ?? '', ['home', 'new', 'different', 'help', 'main'], true) ? $d['postTo'] : '', (is_string($d['postCountry'] ?? null) && $d['postCountry'] !== '') ? (in_array($d['postCountry'], ['DE', 'other'], true) ? $d['postCountry'] : 'intl') : '', $rx]);
    $r = rules();
    return $r['sets'][$r['variants'][$key]];
}

function valid_date(mixed $v): bool
{
    if (!is_string($v) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
        return false;
    }
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
}

function parse_input(array $b, bool $partial = false): array
{
    $r = rules();
    $route = $b['route'] ?? '';
    if (!is_string($route) || !isset($r['routes'][$route]) || !is_array($b['answers'] ?? null) || !is_array($b['data'] ?? null)) {
        fail('INVALID_CASE');
    }
    $answers = [];
    foreach ($r['questions'][$route] as $q) {
        if (isset($b['answers'][$q['key']])) {
            $v = $b['answers'][$q['key']];
            if (!in_array($v, array_column($q['options'], 0), true)) {
                fail('INVALID_ELIGIBILITY');
            }
            $answers[$q['key']] = $v;
        }
    }
    $d = $b['data'];
    if (count($d) > 60) {
        fail('INVALID_FIELD');
    }
    foreach ($d as $v) {
        if (is_string($v) && mb_strlen($v) <= 4000) {
            continue;
        }
        if (is_array($v) && array_is_list($v) && count($v) <= 3 && count(array_filter($v, fn ($i) => is_string($i) && mb_strlen($i) < 50)) === count($v)) {
            continue;
        }
        fail('INVALID_FIELD');
    }
    if (isset($d['changes']) && !is_array($d['changes'])) {
        fail('INVALID_FIELD');
    }
    $active = [];
    $issues = [];
    foreach (field_set($route, $d)['fields'] as $f) {
        $key = $f['key'];
        $v = $d[$key] ?? null;
        if ($v !== null) {
            if (($f['type'] === 'multi') !== is_array($v)) {
                fail('INVALID_FIELD');
            }
            $active[$key] = $v;
        }
        if ($partial) {
            continue;
        }
        $error = null;
        if ($f['required'] && ($v === null || $v === '' || $v === [] || (is_string($v) && trim($v) === ''))) {
            $error = 'Please complete this field.';
        } elseif ($v !== null && $v !== '' && $v !== []) {
            $type = $f['type'];
            if ($type === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $error = 'Enter a valid email address.';
            }
            if ($type === 'account' && !preg_match('/^\d{9}$/', preg_replace('/\s/', '', $v))) {
                $error = 'Enter 9 digits.';
            }
            if (in_array($type, ['date', 'dob'], true) && !valid_date($v)) {
                $error = 'Enter a valid calendar date.';
            }
            if ($type === 'dob' && valid_date($v) && $v > (gmdate('Y') - 18).gmdate('-m-d')) {
                $error = 'You must be at least 18 years old.';
            }
            if ($type === 'dob' && valid_date($v) && $v < (gmdate('Y') - 128).gmdate('-m-d')) {
                $error = 'Enter a date of birth within the last 128 years.';
            }
            if ($type === 'month' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $v)) {
                $error = 'Choose a valid month and year.';
            }
            if (($type === 'date' && valid_date($v)) || ($type === 'month' && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $v))) {
                $min = (gmdate('Y') - 20).gmdate('-m-d');
                $max = $key === 'deathDate' ? gmdate('Y-m-d') : (gmdate('Y') + 1).gmdate('-m-d');
                if ($type === 'month') {
                    $min = substr($min, 0, 7);
                    $max = substr($max, 0, 7);
                }
                if ($v < $min || $v > $max) {
                    $error = $key === 'deathDate' && $v > $max ? 'The date of death cannot be in the future.' : 'Enter a date within the last 20 years or the next 12 months.';
                }
            }
            if ($type === 'select' && !in_array($v, array_column($f['options'], 0), true)) {
                $error = 'Choose an available option.';
            }
            if ($type === 'multi' && array_diff($v, array_column($f['options'], 0))) {
                $error = 'Choose name, address or both.';
            }
        }
        if ($error) {
            $issues[$key] = ['en' => $error, 'de' => 'Bitte prüfen Sie dieses Feld.'];
        }
    }
    if (!$partial) {
        if ($route === 'death' && valid_date($d['deathDate'] ?? null) && ($d['deathDate'] > gmdate('Y-m-d') || $d['deathDate'] < ($d['dob'] ?? ''))) {
            $issues['deathDate'] = ['en' => 'Check the date of death.', 'de' => 'Prüfen Sie das Sterbedatum.'];
        }
        if ($issues) {
            fail('CHECK_FIELDS', 422, $issues);
        }
        $key = implode('|', array_map(fn ($q) => $answers[$q['key']] ?? '', $r['questions'][$route]));
        if (($r['decisions'][$route][$key]['status'] ?? '') !== 'eligible') {
            fail('INELIGIBLE_CASE');
        }
    }
    return ['route' => $route, 'answers' => $answers, 'data' => $active];
}

function valid_iban(string $v): bool
{
    $v = strtoupper(preg_replace('/\s/', '', $v));
    if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $v)) {
        return false;
    }
    $mod = 0;
    foreach (str_split(substr($v, 4).substr($v, 0, 4)) as $ch) {
        foreach (str_split(ctype_alpha($ch) ? (string)(ord($ch) - 55) : $ch) as $n) {
            $mod = ($mod * 10 + (int)$n) % 97;
        }
    }
    return $mod === 1;
}
// The final-check note fills itself: date and who saved the settings once everything else is complete. A note typed by hand is kept.
function launch_check_note(array $prev, array $next): string
{
    $filler = '/^\s*(tba|todo|tbd|-+|\.+|…|xxx)?\s*$/iu';
    $generated = '/^\d{4}-\d{2}-\d{2}, .+: patikrinti teisiniai tekstai/u';
    $note = $next['launchApprovalReference'] ?? '';
    if (!preg_match($filler, $note) && !preg_match($generated, $note)) {
        return $note;
    }
    if (settings_missing($next)) {
        return '';
    }
    $old = $prev['launchApprovalReference'] ?? '';
    $changed = false;
    foreach ($next as $k => $v) {
        if (!in_array($k, ['version', 'liveEnabled', 'launchApprovalReference'], true) && ($prev[$k] ?? '') !== $v) {
            $changed = true;
        }
    }
    if (!$changed && preg_match($generated, $old)) {
        return $old;
    }
    $e = admin_email();
    $name = $e === config()['admin_email'] ? $e : ((record('staff:'.$e)['value']['name'] ?? '') ?: $e);
    return date('Y-m-d').', '.$name.': patikrinti teisiniai tekstai ir banko duomenys – viskas tvarkoje';
}

function settings_missing(array $s): array
{
    $missing = [];
    foreach (array_keys(rules()['emptySettings']) as $key) {
        if (!in_array($key, ['version', 'liveEnabled', 'launchApprovalReference', 'authorityAcceptanceReference'], true) && trim($s[$key] ?? '') === '') {
            $missing[] = $key;
        }
    }
    if (($s['supportEmail'] ?? '') && !filter_var($s['supportEmail'], FILTER_VALIDATE_EMAIL)) {
        $missing[] = 'validEmail';
    }
    if (($s['iban'] ?? '') && !valid_iban($s['iban'])) {
        $missing[] = 'validIban';
    }
    if (($s['vatRate'] ?? '') && (!preg_match('/^\d{1,2}(\.\d{1,2})?$/', $s['vatRate']) || (float)$s['vatRate'] > 30)) {
        $missing[] = 'validVatRate';
    }
    if (($s['invoicePrefix'] ?? '') && !preg_match('/^[A-Z0-9-]{2,16}$/', $s['invoicePrefix'])) {
        $missing[] = 'validInvoicePrefix';
    }
    if (($s['retentionDays'] ?? '') && (!ctype_digit($s['retentionDays']) || (int)$s['retentionDays'] < 1 || (int)$s['retentionDays'] > 99999)) {
        $missing[] = 'validRetentionDays';
    }
    return $missing;
}

function consent(mixed $v): array
{
    if (!is_array($v) || ($v['situation'] ?? null) !== true || ($v['accuracy'] ?? null) !== true || ($v['authority'] ?? null) !== true || ($v['terms'] ?? null) !== true || ($v['early'] ?? null) !== true) {
        fail('CONSENT_REQUIRED');
    }
    return $v;
}

function readiness(array $c): array
{
    $b = [];
    try {
        parse_input($c);
    } catch (AppError) {
        $b[] = 'details';
    }
    if (!$c['order']) {
        $b[] = 'order';
    }
    $o = $c['operations'] ?? [];
    $checks = $c['checks'];
    if (isset($o['hold'])) {
        $b[] = 'hold';
    }
    if (isset($o['withdrawal'])) {
        $b[] = 'withdrawal';
    }
    if (($o['transmission']['status'] ?? '') === 'uncertain') {
        $b[] = 'transmission';
    }
    if (($c['data']['postTo'] ?? '') === 'help') {
        $b[] = 'correspondence';
    }
    if (empty($checks['authorityVerified']) || empty($checks['authorityReference'])) {
        $b[] = 'authority';
    }
    if (empty($checks['contractDeliveredAt']) || empty($checks['deliveryReference'])) {
        $b[] = 'contractDelivery';
    }
    if (empty($c['order']['consent']['early']) && (!isset($checks['withdrawalEndsAt'],$checks['withdrawalPeriodReference']) || strtotime($checks['withdrawalEndsAt']) >= time())) {
        $b[] = 'start';
    }
    if (($c['route'] === 'death' || ($c['data']['forWhom'] ?? '') === 'other') && (empty($checks['roleVerified']) || empty($checks['roleReference']))) {
        $b[] = 'representation';
    }
    if ($c['route'] === 'death') {
        if (($c['data']['remaining'] ?? '') === 'unsure' && empty($checks['circumstancesVerified'])) {
            $b[] = 'occupants';
        }
        if (!valid_date($checks['officialDate'] ?? null) || empty($checks['dateReason'])) {
            $b[] = 'officialDate';
        }
    }
    $docs = field_set($c['route'], $c['data'])['documents'];
    $docs[] = ['id' => 'authority', 'required' => true];
    foreach ($docs as $d) {
        if ($d['required'] && !array_filter($c['documents'], fn ($v) => $v['kind'] === $d['id'] && $v['status'] === 'verified')) {
            $b[] = 'document:'.$d['id'];
        }
    }
    return $b;
}

function instant(mixed $v): string
{
    $v = text($v, 20, 24);
    if (!preg_match('/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.\d{3})?Z$/', $v, $m) || !valid_date($m[1]) || (int)$m[2] > 23 || (int)$m[3] > 59 || (int)$m[4] > 59) {
        fail('INVALID_DATE');
    }
    return $v;
}
