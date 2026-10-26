<?php

const POA_BODY = ['de' => 'Bevollmächtigte und Umfang
Ich bevollmächtige {PFULL}, mich gegenüber dem ARD ZDF Deutschlandradio Beitragsservice und der für mein Beitragskonto zuständigen Landesrundfunkanstalt ausschließlich bei dem oben bezeichneten Vorgang zu vertreten.
Die Vollmacht umfasst die Übertragung meiner Angaben in die erforderlichen Formulare, deren Einreichung in meinem Namen zusammen mit den bezeichneten Nachweisen sowie die hierfür bis zur Einreichung erforderliche administrative Kommunikation. Offensichtliche Übertragungsfehler dürfen berichtigt werden. Änderungen meines Antragsinhalts oder meiner Sachverhaltsangaben bedürfen meiner vorherigen Bestätigung.
Bei einem Auftrag zur Befreiung einer Nebenwohnung umfasst die Vollmacht auch deren erforderliche Anmeldung, jedoch nur, wenn diese oben ausdrücklich als Bestandteil des Auftrags bezeichnet ist. Andere Wohnungen oder eigenständige Folgeaufträge sind nicht erfasst.

Grenzen der Vollmacht
Die Vollmacht umfasst keine Rechtsberatung, Rechtsbehelfe, Vertretung vor Gericht, Vergleiche, Schuldanerkenntnisse, Anspruchsverzichte, Entgegennahme von Erstattungszahlungen oder Verfügungen über Bankkonten. Sie ist kein SEPA-Lastschriftmandat. Bei Änderungen der Beitragszahlung dürfen nur die von mir bezeichneten Zahlungsangaben und ein vom berechtigten Kontoinhaber gesondert erteiltes Mandat übermittelt werden.
Eine Vollmacht zur Entgegennahme förmlicher Bescheide oder Zustellungen wird nicht erteilt. Diese sollen unmittelbar an mich gerichtet werden. Eine Vertretung anderer Haushaltsangehöriger ist nicht umfasst. {P} darf durch ihre vertretungsberechtigten Personen und intern befugten Beschäftigten handeln; eine Unterbevollmächtigung selbständiger Dritter ist nicht gestattet.

Dauer und Widerruf
Die Vollmacht gilt ab ihrer Erteilung bis zur Einreichung sämtlicher oben bezeichneter Unterlagen, längstens bis zum {UNTIL}. Sie endet vorher, wenn ich sie widerrufe oder der zugrunde liegende Auftrag beendet wird. Eine spätere erneute Einreichung bedarf einer neuen Vollmacht.
Ich kann die Vollmacht jederzeit mit Wirkung für die Zukunft gegenüber {P} oder der Stelle, gegenüber der die Vertretung erfolgen soll, widerrufen, insbesondere per E-Mail an {MAIL}. {P} stellt nach Zugang des Widerrufs weitere Vertretungshandlungen ein und informiert bereits über die Vollmacht unterrichtete Empfänger unverzüglich über deren Erlöschen. Bereits wirksam vorgenommene Handlungen werden dadurch nicht rückwirkend aufgehoben.
Die Erteilung dieser Vollmacht begründet für sich genommen keine Zahlungspflicht und führt nicht zum Verlust des gesetzlichen Widerrufsrechts bezüglich des Servicevertrags. Der Widerruf der Vollmacht ist von der Beendigung dieses Vertrags zu unterscheiden; der Inhalt meiner Erklärung ist maßgeblich.

Erteilung im Online-Bestellvorgang: durch gesondertes aktives Ankreuzen der Erklärung „Ich erteile die Vollmacht für diesen Auftrag“.', 'en' => 'Authorised representative and scope
I authorise {PFULL}, to represent me before ARD ZDF Deutschlandradio Beitragsservice and the regional broadcasting institution responsible for my contribution account exclusively in the matter identified above.
The authority covers transferring my information into the necessary forms, submitting them in my name with the identified supporting documents, and the administrative communication necessary for that purpose up to submission. Obvious transcription errors may be corrected. Changes to the substance of my application or my factual statements require my prior confirmation.
For a secondary-residence exemption order, the authority also covers any necessary registration of that residence, but only where registration is expressly identified above as part of the order. Other dwellings and separate subsequent orders are not covered.

Limits of authority
This authority does not cover legal advice, legal challenges, representation in court, settlements, acknowledgements of debt, waivers of claims, receipt of refunds or operation of bank accounts. It is not a SEPA direct debit mandate. For contribution-payment changes, only the payment details specified by me and a mandate separately granted by the authorised account holder may be transmitted.
No authority is granted to receive formal administrative decisions or service of documents. These should be addressed directly to me. Representation of other household members is not included. {P} may act through its authorised corporate representatives and internally authorised employees; it may not appoint independent third parties as substitute representatives.

Duration and revocation
This authority applies from the time it is granted until all the documents identified above have been submitted, but no later than {UNTIL}. It ends earlier if I revoke it or the underlying order is terminated. A later resubmission requires a new power of attorney.
I may revoke this authority at any time with effect for the future by notifying {P} or the body before which representation is to take place, in particular by emailing {MAIL}. On receipt of revocation, {P} must stop further acts of representation and promptly notify recipients already informed of the authority that it has ended. Acts already validly performed are not retrospectively undone.
Granting this authority does not itself create an obligation to pay or extinguish the statutory right of withdrawal from the service contract. Revoking the authority is distinct from ending that contract; the substance of my declaration determines its meaning.

Grant in the online checkout: by actively selecting the separate declaration “I grant the power of attorney for this order”.'];
function poa_address(array $d, string $p): string
{
    return implode(', ', array_filter([trim(((string)($d[$p.'Street'] ?? '')).' '.((string)($d[$p.'Number'] ?? ''))), trim((string)($d[$p.'Extra'] ?? '')), trim(((string)($d[$p.'Postcode'] ?? '')).' '.((string)($d[$p.'City'] ?? ''))), $p === 'new' ? trim((string)($d['countryName'] ?? '')) : ''], 'strlen'));
}

function authority_text(array $c, array $s, string $ref, string $at, string $lang): string
{
    $d = $c['data'];
    $r = $c['route'];
    $set = field_set($r, $d);
    $grantor = ($r === 'death' || ($d['forWhom'] ?? '') === 'other') ? trim($d['applicantFirst'].' '.$d['applicantLast']) : trim($d['firstName'].' '.$d['lastName']);
    $post = ['different' => 'post', 'new' => 'new', 'main' => 'main'][$d['postTo'] ?? ''] ?? 'home';
    $until = gmdate('d.m.Y', strtotime(substr($at, 0, 10).' +6 months'));
    $reg = preg_match('/\b\d{9}\b/', (string)($s['taxIdentifier'] ?? ''), $m) ? $m[0] : '';
    $addr = preg_replace('/\s*\R\s*/', ', ', trim($s['providerAddress']));
    $flats = [poa_address($d, 'home')];
    if ($r === 'change' && !empty($d['newStreet'])) {
        $flats[] = poa_address($d, 'new');
    }
    $out = [];
    foreach ($lang === 'en' ? ['de', 'en'] : ['de'] as $l) {
        $en = $l === 'en';
        $title = rules()['routes'][$r]['title'][$l];
        if ($r === 'death' && ($d['remaining'] ?? '') === 'yes' && ($d['remainingWho'] ?? '') === 'self') {
            $who = ($d['remainingWho'] ?? '') === 'other' ? trim(($d['remainingFirst'] ?? '').' '.($d['remainingLast'] ?? '')) : ($en ? 'the person placing the order' : 'auftraggebende Person');
            $title .= ($en ? ' and registration of the dwelling for the remaining resident (' : ' sowie Anmeldung der Wohnung für die verbleibende Person (').$who.')';
        }
        if ($r === 'second' && ($d['secondRegistered'] ?? '') !== 'yes') {
            $title .= $en ? ', including registration of the second home where necessary' : ' einschließlich ihrer Anmeldung, soweit erforderlich';
        }
        $change = [];
        foreach ($set['fields'] as $f) {
            $v = $d[$f['key']] ?? '';
            if ($v === '' || $v === []) {
                continue;
            }
            if (in_array($f['type'], ['date', 'month'], true)) {
                $change[] = $f['label'][$l].': '.$v;
            } elseif ($f['key'] === 'changes') {
                $names = [];
                foreach ($f['options'] as [$code,$label]) {
                    if (in_array($code, (array)$v, true)) {
                        $names[] = $label[$l];
                    }
                }
                $change[] = ($en ? 'Change: ' : 'Änderung: ').implode(', ', $names);
            }
        }
        $docs = array_map(fn ($x) => $x['title'][$l], $set['documents']);
        $P = $s['providerName'];
        $full = $P.', '.$addr.($reg !== '' ? ($en ? ', registration number ' : ', Registrierungsnummer ').$reg : '').($en ? ', email: ' : ', E-Mail: ').$s['supportEmail'];
        $head = $en ? "Power of attorney for one broadcasting contribution matter\n\nPrincipal and matter\nFull name: $grantor\nAddress: ".poa_address($d, $post)."\nContribution number, if available: ".(($d['account'] ?? '') ?: 'none (new registration)')."\nOrder reference: $ref\nSelected procedure: $title\nAffected dwelling or dwellings: ".implode('; ', $flats)."\nRelevant date and specific change: ".($change ? implode('; ', $change) : 'see order details')."\nDocuments to be submitted: the form for the procedure identified above".($docs ? ' and, where provided: '.implode('; ', $docs) : '')."\n\n" : "Vollmacht für einen einzelnen Rundfunkbeitragsauftrag\n\nVollmachtgebende Person und Vorgang\nVollständiger Name: $grantor\nAnschrift: ".poa_address($d, $post)."\nBeitragsnummer, soweit vorhanden: ".(($d['account'] ?? '') ?: 'keine (Neuanmeldung)')."\nAuftragsnummer: $ref\nAusgewählter Vorgang: $title\nBetroffene Wohnung beziehungsweise Wohnungen: ".implode('; ', $flats)."\nMaßgebliches Datum und konkrete Änderung: ".($change ? implode('; ', $change) : 'siehe Auftragsangaben')."\nEinzureichende Unterlagen: Formular für den oben bezeichneten Vorgang".($docs ? ' sowie, soweit vorgelegt: '.implode('; ', $docs) : '')."\n\n";
        $out[] = $head.strtr(POA_BODY[$l], ['{PFULL}' => $full, '{P}' => $P, '{MAIL}' => $s['supportEmail'], '{UNTIL}' => $until]).($en ? "\nRecord: order reference $ref · version ".POA_VERSION." · $at (UTC)\nMethod: " : "\nDokumentation: Auftragsnummer $ref · Fassung ".POA_VERSION." · $at (UTC)\nVerfahren: ").$s['authorityMethod'];
    }
    return implode("\n\n— English translation for information; the German text above is binding —\n\n", $out);
}

const POA_VERSION = 'vollmacht-2026-10-25b';
function make_order(array $c, array $s, array $consent, string $lang): array
{
    $d = $c['data'];
    $grantor = ($c['route'] === 'death' || ($d['forWhom'] ?? '') === 'other') ? ($d['applicantFirst'].' '.$d['applicantLast']) : ($d['firstName'].' '.$d['lastName']);
    $at = now();
    $ref = $c['order']['reference'] ?? new_reference();
    $authority = authority_text($c, $s, $ref, $at, $lang);
    $o = ['at' => $at, 'reference' => $ref, 'feeCents' => 4900, 'consent' => $consent, 'authorityText' => $authority, 'declarations' => (isset(rules()['situations'][$c['route']][$lang]) ? ['situation' => rules()['situations'][$c['route']][$lang]] : []) + rules()['declarations'][$lang], 'settingsVersion' => $s['version'], 'permittedStartAt' => $consent['early'] ? $at : '', 'language' => $lang];
    foreach (['termsText', 'withdrawalText', 'privacyText', 'providerName', 'providerAddress', 'supportEmail', 'iban', 'bic', 'beneficiary', 'authorityMethod'] as $k) {
        $o[$k] = $s[$k];
    }
    return $o;
}

function new_reference(): string
{
    for ($i = 0;$i < 20;$i++) {
        $ref = 'RB-'.strtoupper(bin2hex(random_bytes(4)));
        if (!sql("SELECT 1 FROM rb_records WHERE kind='case' AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.order.reference'))=? LIMIT 1", [$ref])->fetch()) {
            return $ref;
        }
    }
    fail('REFERENCE_UNAVAILABLE', 503);
}
// Order confirmation (and power of attorney) as HTML for dompdf: CSS 2.1, tables, no web fonts, so Noto Sans is used.
const DOC_CSS = '@page{margin:22mm 18mm 20mm}body{font-family:"Noto Sans",sans-serif;font-size:9.5pt;color:#1E2B28;line-height:1.3}
#foot{position:fixed;bottom:-12mm;left:0;right:0;font-size:7.5pt;color:#5E6E6A;border-top:1px solid #E4DED3;padding-top:5px}#foot .pg:after{content:counter(page)}
table{width:100%;border-collapse:collapse}td,th{vertical-align:top;text-align:left}p{margin:0 0 8px;overflow-wrap:anywhere}
.head td{padding:0 0 12px}.brand{font-size:15pt;font-weight:bold;color:#123A34}.mark{display:inline-block;width:7px;height:16px;background:#F2A65A;margin-right:8px}
.doc{text-align:right;color:#5E6E6A;font-size:8.5pt}.doc b{display:block;font-size:12pt;color:#123A34}.rule{height:3px;background:#0F6E62;margin:0 0 16px}
.sum td{width:25%;padding:8px 10px;background:#F7F4EE;border:3px solid #fff}.k{display:block;line-height:1.2;margin-bottom:3px;font-size:7.5pt;color:#5E6E6A;text-transform:uppercase;letter-spacing:.06em}.v{font-weight:bold;color:#123A34;font-size:10pt}
h2{font-size:10.5pt;color:#0F6E62;margin:18px 0 6px;padding-bottom:4px;border-bottom:1px solid #E4DED3;page-break-after:avoid}h2 span{color:#5E6E6A;font-weight:normal}
.pay{background:#F7F4EE;border:1px solid #E4DED3}.pay td{padding:12px 14px}.kv td{padding:4px 0;border:0}.kv td.l{width:38%;color:#5E6E6A;font-size:8.5pt}.ref{background:#FBF3E8;border-left:3px solid #F2A65A;padding:1px 6px;font-weight:bold}
.data tr{page-break-inside:avoid}.data th,.data td{padding:5px 8px;border-bottom:1px solid #EEE9E0}.data th{width:38%;font-weight:normal;color:#5E6E6A}.data tr.z td,.data tr.z th{background:#FBFAF7}
.ok{color:#0F6E62;font-weight:bold;white-space:nowrap}.no{color:#5E6E6A;white-space:nowrap}.legal{font-size:8pt;color:#33433F}.box{border:1px solid #E4DED3;padding:12px 14px}
.sign td{padding:26px 16px 0 0;width:50%}.sign div{border-top:1px solid #1E2B28;padding-top:3px;font-size:8pt;color:#5E6E6A}.note{font-size:8pt;color:#5E6E6A;margin-top:18px}';
// The order confirmation is only in the customer's language. The power of attorney document keeps German labels first
// (it goes to Beitragsservice), with the customer's language after a slash.
function doc_label(string $key, string $l, bool $authority, bool $span = false): string
{
    $own = h(mt($key, $l));
    if (!$authority || $l === 'de') {
        return $own;
    }
    $de = h(mt($key, 'de'));
    return $span ? $de.' <span>/ '.$own.'</span>' : $de.' / '.$own;
}

function document_html(array $c, bool $authority = false): string
{
    $o = $c['order'];
    if (!$o) {
        fail('ORDER_REQUIRED');
    }
    $l = rb_lang($o['language'] ?? 'de');
    $d = $c['data'];
    $route = rules()['routes'][$c['route']]['title'];
    $a = $authority;
    $at = ($t = strtotime($o['at'])) ? gmdate('d.m.Y, H:i', $t).' UTC' : $o['at'];
    $fee = mail_money((int)($o['feeCents'] ?? 4900), $l);
    $doc = $authority ? 'pdf.authority' : 'pdf.confirmation';
    $html = '<!doctype html><html lang="'.h($l).'"><head><meta charset="utf-8"><style>'.DOC_CSS.'</style></head><body>'
     .'<div id="foot"><table><tr><td>'.h($o['providerName']).' · '.h(trim(preg_replace('/\s*\R\s*/', ', ', trim($o['providerAddress'])), ', ')).' · '.h($o['supportEmail']).'</td><td style="text-align:right;white-space:nowrap">'.h($o['reference']).' · '.doc_label('pdf.page', $l, $a).' <span class="pg"></span></td></tr></table></div>'
     .'<table class="head"><tr><td><span class="mark"></span><span class="brand">Rundfunkbeitrag Support</span></td><td class="doc"><b>'.h(mt($doc, $a ? 'de' : $l)).'</b>'.($a && $l !== 'de' ? h(mt($doc, $l)) : '').'</td></tr></table><div class="rule"></div>'
     .'<table class="sum"><tr><td><span class="k">'.doc_label('mail.order', $l, $a).'</span><span class="v">'.h($o['reference']).'</span></td><td style="width:35%"><span class="k">'.doc_label('pay.service', $l, $a).'</span><span class="v">'.h(pick_lang($route, $l)).'</span></td><td><span class="k">'.doc_label('pdf.date', $l, $a).'</span><span class="v">'.h($at).'</span></td><td style="width:15%"><span class="k">'.doc_label('pdf.price', $l, $a).'</span><span class="v">'.h($fee).'</span></td></tr></table>';
    if (!$authority) {
        $who = ($c['route'] === 'death' || ($d['forWhom'] ?? '') === 'other') ? trim(($d['applicantFirst'] ?? '').' '.($d['applicantLast'] ?? '')) : trim(($d['firstName'] ?? '').' '.($d['lastName'] ?? ''));
        $html .= '<table style="margin-top:14px"><tr><td style="width:50%;padding-right:12px"><h2 style="margin-top:0">'.h(mt('pdf.provider', $l)).'</h2><p>'.h($o['providerName']).'<br>'.nl2br(h($o['providerAddress'])).'<br>'.h($o['supportEmail']).'</p></td><td style="width:50%;padding-left:12px"><h2 style="margin-top:0">'.h(mt('pdf.customer', $l)).'</h2><p>'.h($who).'<br>'.h((string)($d['email'] ?? '')).'</p></td></tr></table>';
        $qr = girocode_data_uri($o);
        $html .= '<h2>'.h(mt('pdf.payment', $l)).'</h2><table class="pay"><tr><td><table class="kv">'
         .'<tr><td class="l">'.h(mt('pay.recipient', $l)).'</td><td>'.h($o['beneficiary']).'</td></tr><tr><td class="l">IBAN</td><td><b>'.h(trim(chunk_split(strtoupper(preg_replace('/\s/', '', $o['iban'])), 4, ' '))).'</b></td></tr><tr><td class="l">BIC</td><td>'.h($o['bic']).'</td></tr>'
         .'<tr><td class="l">'.h(mt('pay.amount', $l)).'</td><td><b>'.h($fee).'</b></td></tr><tr><td class="l">'.h(mt('pay.reference', $l)).'</td><td><span class="ref">'.h($o['reference']).'</span></td></tr></table>'
         .'<p style="margin:8px 0 0;font-size:8pt;color:#5E6E6A">'.h(mt('pdf.referenceNote', $l)).'</p></td>'
         .($qr ? '<td style="width:118px;text-align:center"><img src="'.$qr.'" width="110" height="110" alt="GiroCode"><br><span style="font-size:7pt;line-height:1.2;color:#5E6E6A">'.h(mt('pdf.girocode', $l)).'</span></td>' : '').'</tr></table>';
        $html .= '<h2>'.h(mt('pdf.data', $l)).'</h2><table class="data">';
        $i = 0;
        foreach (field_set($c['route'], $d)['fields'] as $f) {
            $v = $d[$f['key']] ?? '';
            if ($v === '' || $v === []) {
                continue;
            }
            if (isset($f['options'])) {
                foreach ($f['options'] as [$code,$label]) {
                    if ($v === $code) {
                        $v = pick_lang($label, $l);
                        break;
                    }
                }
            }
            $html .= '<tr'.($i++ % 2 ? ' class="z"' : '').'><th>'.h(pick_lang($f['label'], $l)).'</th><td>'.h(is_array($v) ? implode(', ', $v) : $v).'</td></tr>';
        }
        $html .= '</table>';
        $html .= '<h2>'.h(mt('pdf.declarations', $l)).'</h2><table class="data">';
        foreach ($o['declarations'] as $key => $label) {
            $html .= '<tr><td style="width:24%">'.(!empty($o['consent'][$key]) ? '<span class="ok">'.h(mt('pdf.accepted', $l)).'</span>' : '<span class="no">'.h(mt('pdf.notRequested', $l)).'</span>').'</td><td>'.h($label).'</td></tr>';
        }
        $html .= '</table>';
        foreach (['termsText' => 'pdf.terms', 'withdrawalText' => 'pdf.withdrawal', 'privacyText' => 'pdf.privacy'] as $key => $title) {
            $html .= '<h2>'.h(mt($title, $l)).'</h2><p class="legal">'.nl2br(h($o[$key])).'</p>';
        }
        $html .= '<h2 style="page-break-before:always;margin-top:0">'.h(mt('pdf.authority', $l)).'</h2>';
    } else {
        $html .= '<h2>'.doc_label('pdf.authority', $l, $a, true).'</h2>';
    }
    $html .= '<div class="box"><p class="legal" style="font-size:8.5pt">'.nl2br(h($o['authorityText'])).'</p></div><table class="sign"><tr><td><div>'.doc_label('pdf.place', $l, $a).'</div></td><td><div>'.doc_label('pdf.signature', $l, $a).'</div></td></tr></table>'
     .'<p class="note">'.implode('<br>', array_map(fn ($x) => h(mt('pdf.note', $x)), array_unique($a ? ['de', $l] : [$l]))).'</p></body></html>';
    return $html;
}
// Invoice in the customer's language (net/VAT lines only when the VAT rate is above 0 %), from the snapshot taken when it was issued (later setting or data changes don't alter it).
function invoice_number(array $i): string
{
    $p = trim((string)($i['snapshot']['settings']['invoicePrefix'] ?? ''));
    return ($p !== '' ? $p.'-' : '').$i['sequence'];
}

function invoice_html(array $i): string
{
    $sn = $i['snapshot'];
    $o = $sn['order'];
    $s = $sn['settings'];
    $l = rb_lang($o['language'] ?? 'de');
    $day = fn (?string $v) => $v ? (new DateTimeImmutable($v))->setTimezone(new DateTimeZone('Europe/Berlin'))->format('d.m.Y') : '';
    $no = invoice_number($i);
    $gross = (int)($o['feeCents'] ?? 4900);
    $rate = (float)str_replace(',', '.', (string)($s['vatRate'] ?? '0'));
    $net = (int)round($gross / (1 + $rate / 100));
    $rateText = rtrim(rtrim(number_format($rate, 2, mt('dec', $l), ''), '0'), mt('dec', $l));
    $route = rules()['routes'][$sn['route'] ?? '']['title'] ?? null;
    $service = $route ? pick_lang($route, $l) : '';
    $addr = trim(preg_replace('/\s*\R\s*/', ', ', trim($s['providerAddress'])), ', ');
    $html = '<!doctype html><html lang="'.h($l).'"><head><meta charset="utf-8"><style>'.DOC_CSS.'.items th,.items td{padding:7px 8px;border-bottom:1px solid #EEE9E0}.items th{font-weight:normal;color:#5E6E6A;font-size:8.5pt}.items .r{text-align:right;white-space:nowrap}.items tr.t td{border-bottom:0;padding-top:4px;padding-bottom:4px}.items tr.g td{font-weight:bold;color:#123A34;font-size:10.5pt;border-top:2px solid #0F6E62}</style></head><body>'
     .'<div id="foot"><table><tr><td>'.h($s['providerName']).' · '.h($addr).' · '.h($s['supportEmail']).'</td><td style="text-align:right;white-space:nowrap">'.h($no).' · '.h(mt('pdf.page', $l)).' <span class="pg"></span></td></tr></table></div>'
     .'<table class="head"><tr><td><span class="mark"></span><span class="brand">Rundfunkbeitrag Support</span></td><td class="doc"><b>'.h(mt('inv.title', $l)).'</b>'.h($no).'</td></tr></table><div class="rule"></div>'
     .'<table class="sum"><tr><td><span class="k">'.h(mt('inv.number', $l)).'</span><span class="v">'.h($no).'</span></td><td><span class="k">'.h(mt('inv.date', $l)).'</span><span class="v">'.h($day($i['created_at'])).'</span></td><td><span class="k">'.h(mt('inv.serviceDate', $l)).'</span><span class="v">'.h($day($sn['serviceDate'])).'</span></td><td><span class="k">'.h(mt('pay.orderNumber', $l)).'</span><span class="v">'.h($o['reference']).'</span></td></tr></table>'
     .'<table style="margin-top:14px"><tr><td style="width:50%;padding-right:12px"><h2 style="margin-top:0">'.h(mt('pdf.provider', $l)).'</h2><p>'.h($s['providerName']).'<br>'.nl2br(h(trim($s['providerAddress']))).(trim((string)($s['taxIdentifier'] ?? '')) !== '' ? '<br>'.h($s['taxIdentifier']) : '').'<br>'.h($s['supportEmail']).'</p></td><td style="width:50%;padding-left:12px"><h2 style="margin-top:0">'.h(mt('inv.billedTo', $l)).'</h2><p>'.nl2br(h($sn['billingAddress'])).'</p></td></tr></table>'
     .'<table class="items" style="margin-top:18px"><tr><th>'.h(mt('inv.item', $l)).'</th><th class="r" style="width:12%">'.h(mt('inv.qty', $l)).'</th><th class="r" style="width:20%">'.h(mt('inv.amount', $l)).'</th></tr>'
     .'<tr><td>'.h(rtrim(mt('inv.itemText', $l, ['service' => $service]), ': ')).'<br><span style="font-size:8.5pt;color:#5E6E6A">'.h(mt('inv.itemRef', $l, ['ref' => $o['reference']])).'</span></td><td class="r">1</td><td class="r">'.h(mail_money($net, $l)).'</td></tr>'
     .($rate > 0 ? '<tr class="t"><td colspan="2" class="r">'.h(mt('inv.net', $l)).'</td><td class="r">'.h(mail_money($net, $l)).'</td></tr><tr class="t"><td colspan="2" class="r">'.h(mt('inv.vat', $l, ['rate' => $rateText])).'</td><td class="r">'.h(mail_money($gross - $net, $l)).'</td></tr>' : '')
     .'<tr class="g"><td colspan="2" class="r">'.h(mt('inv.total', $l)).'</td><td class="r">'.h(mail_money($gross, $l)).'</td></tr></table>'
     .'<p style="margin-top:16px"><b>'.h(!empty($sn['paidAt']) ? mt('inv.paid', $l, ['date' => $day($sn['paidAt'])]) : mt('inv.paidNoDate', $l)).'</b></p>'
     .(trim((string)($s['taxStatement'] ?? '')) !== '' ? '<p class="legal">'.nl2br(h(trim($s['taxStatement']))).'</p>' : '')
     .'<p class="note">'.h(mt('pdf.note', $l)).'</p></body></html>';
    return $html;
}
// The invoice as an email attachment: PDF, or the HTML version when the PDF font can't show a character.
function invoice_attachment(array $i): array
{
    $name = $i['snapshot']['order']['reference'].'-'.mt('inv.file', rb_lang($i['snapshot']['order']['language'] ?? 'de')).'-'.invoice_number($i);
    $html = invoice_html($i);
    try {
        return ['filename' => $name.'.pdf', 'content' => base64_encode(pdf_bytes($html))];
    } catch (AppError $e) {
        if ($e->reason !== 'PDF_USE_HTML') {
            throw $e;
        }
        return ['filename' => $name.'.html', 'content' => base64_encode($html)];
    }
}

function pdf_bytes(string $html): string
{
    require_once private_root().'/vendor/autoload.php';
    $options = new Dompdf\Options();
    $options->set('isRemoteEnabled', false);
    $options->set('isPhpEnabled', false);
    $options->set('isJavascriptEnabled', false);
    $coverage = array_flip(json_decode(file_get_contents(__DIR__.'/fonts/coverage.json'), true));
    foreach (preg_split('//u', strip_tags(html_entity_decode($html, ENT_QUOTES, 'UTF-8')), -1, PREG_SPLIT_NO_EMPTY) as $char) {
        if (!in_array($char, ["\n", "\r", "\t"], true) && !isset($coverage[mb_ord($char, 'UTF-8')])) {
            fail('PDF_USE_HTML', 422);
        }
    }
    $options->set('defaultFont', 'Noto Sans');
    $options->set('tempDir', private_root().'/data/tmp');
    $options->set('fontCache', private_root().'/data/fonts');
    $options->set('chroot', private_root().'/data');
    foreach (['tmp', 'fonts'] as $d) {
        if (!is_dir(private_root().'/data/'.$d)) {
            mkdir(private_root().'/data/'.$d, 0700, true);
        }
    }
    $dompdf = new Dompdf\Dompdf($options);
    $font = private_root().'/data/fonts/NotoSans.ttf';
    if (!is_file($font)) {
        copy(__DIR__.'/fonts/NotoSans.ttf', $font);
        chmod($font, 0600);
    }
    $dompdf->getFontMetrics()->registerFont(['family' => 'Noto Sans', 'style' => 'normal', 'weight' => 'normal'], $font);
    $bold = private_root().'/data/fonts/NotoSans-SemiBold.ttf';
    if (!is_file($bold)) {
        copy(__DIR__.'/fonts/NotoSans-SemiBold.ttf', $bold);
        chmod($bold, 0600);
    }
    $dompdf->getFontMetrics()->registerFont(['family' => 'Noto Sans', 'style' => 'normal', 'weight' => 'bold'], $bold);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4');
    $dompdf->render();
    return $dompdf->output();
}

function download(string $content, string $mime, string $name): never
{
    if (db()->inTransaction()) {
        db()->commit();
    }
    header('Content-Type: '.$mime);
    header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($name));
    header('Cache-Control: private, no-store');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    echo $content;
    exit;
}
