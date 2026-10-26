<?php

// Street suggestions for the order form. Only the typed text goes to OpenPLZ (open data, no key); the visitor's IP stays here.
function address_query(string $q): array
{
    $number = '';
    $terms = [];
    foreach (preg_split('/[\s,]+/u', $q, -1, PREG_SPLIT_NO_EMPTY) as $t) {
        if ($number === '' && preg_match('/^\d{1,4}[a-zA-Z]?(?:[-\/]\d{1,4}[a-zA-Z]?)?$/', $t)) {
            $number = $t;
        } else {
            $terms[] = $t;
        }
    }
    return [implode(' ', $terms), $number];
}

function address_rows(string $url): array
{
    [$code,$rows] = remote_http($url, 'GET', null, ['Accept: application/json']);
    if ($code !== 200 || !is_array($rows)) {
        throw new RuntimeException();
    }
    return array_is_list($rows) ? $rows : [];
}

function address_api(): never
{
    $q = $_GET['q'] ?? '';
    if (!is_string($q)) {
        response_json(['error' => 'INVALID_QUERY', 'suggestions' => []], 400);
    }
    $q = trim($q);
    rate('address:'.($_SERVER['REMOTE_ADDR'] ?? 'local'), 300, 3600);
    if (mb_strlen($q) < 3 || mb_strlen($q) > 100 || preg_match('/[\x00-\x1f]/', $q)) {
        response_json(['error' => 'INVALID_QUERY', 'suggestions' => []], 400);
    }[$term,$number] = address_query($q);
    if (mb_strlen($term) < 3) {
        response_json(['suggestions' => [], 'source' => 'OpenPLZ']);
    }
    try {
        $rows = address_rows('https://openplzapi.org/de/FullTextSearch?searchTerm='.rawurlencode($term).'&page=1&pageSize=10');
        // Full-text search matches whole words; while the visitor is still typing a word, fall back to a street-name prefix search.
        if (!$rows && !preg_match('/\d/', $term)) {
            $first = mb_strtoupper(mb_substr($term, 0, 1)).mb_substr($term, 1);
            $rows = address_rows('https://openplzapi.org/de/Streets?name='.rawurlencode('^'.preg_quote($first)).'&page=1&pageSize=10');
        }
        $suggestions = [];
        foreach ($rows as $r) {
            $street = $r['name'] ?? '';
            $postcode = $r['postalCode'] ?? '';
            $city = $r['locality'] ?? '';
            $part = $r['suburb'] ?? ($r['borough'] ?? '');
            if (!is_string($part) || $part === $city) {
                $part = '';
            }
            if (!is_string($street) || !is_string($postcode) || !is_string($city) || !preg_match('/^\d{5}$/', $postcode) || $street === '') {
                continue;
            }
            $id = hash('sha256', $street."\n".$postcode."\n".$city);
            if (isset($suggestions[$id])) {
                continue;
            }
            $suggestions[$id] = ['id' => $id, 'street' => $street, 'houseNumber' => $number, 'postalCode' => $postcode, 'locality' => $city, 'district' => $part, 'label' => $street.($number !== '' ? ' '.$number : '').', '.$postcode.' '.$city];
            if (count($suggestions) >= 8) {
                break;
            }
        }
        response_json(['suggestions' => array_values($suggestions), 'source' => 'OpenPLZ']);
    } catch (Throwable) {
        response_json(['error' => 'DIRECTORY_UNAVAILABLE', 'suggestions' => []], 503);
    }
}
