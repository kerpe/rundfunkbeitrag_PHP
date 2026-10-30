<?php

// Page titles, descriptions, guide pages (/ratgeber, /guide) and sitemap.xml.
function seo_articles(): array
{
    static $a = null;
    return $a ??= require __DIR__.'/articles.php';
}

function seo_codes(): array
{
    return ['zusammenziehen' => 'P-04', 'ausland' => 'P-05', 'weitere-wohnung-aufgeben' => 'P-06', 'sterbefall' => 'P-07', 'anmelden' => 'P-01', 'daten-aendern' => 'P-02', 'zahlung-aendern' => 'P-03', 'zweitwohnung' => 'P-08'];
}

function seo_services(): array
{
    return [
        'zusammenziehen' => ['Rundfunkbeitrag abmelden beim Zusammenziehen', 'Sie ziehen zu jemandem, der bereits zahlt? Wir melden Ihr Beitragskonto ab, damit nicht doppelt gezahlt wird. Einmalig 49 €, offizielle Formulare kostenlos.'],
        'ausland' => ['Rundfunkbeitrag abmelden beim Umzug ins Ausland', 'Dauerhaft ins Ausland? Wir melden Ihren Rundfunkbeitrag ab, auf Wunsch auch auf Englisch. Einmalig 49 €, offizielles Formular kostenlos.'],
        'weitere-wohnung-aufgeben' => ['Eine von mehreren Wohnungen abmelden', 'Sie geben eine von mehreren Wohnungen auf? Wir melden sie beim Beitragsservice ab. Einmalig 49 €, offizielles Formular kostenlos.'],
        'sterbefall' => ['Rundfunkbeitrag nach Todesfall abmelden', 'Wir melden das Beitragskonto einer verstorbenen Person für Angehörige ab. Einmalig 49 €, offizielles Formular kostenlos.'],
        'anmelden' => ['Wohnung erstmals beim Rundfunkbeitrag anmelden', 'Erste eigene Wohnung oder neue WG? Wir melden die Wohnung beim Beitragsservice an. Einmalig 49 €, offizielles Formular kostenlos.'],
        'daten-aendern' => ['Umzug oder Namensänderung beim Rundfunkbeitrag melden', 'Umzug oder neuer Name? Wir teilen die Änderung dem Beitragsservice mit. Einmalig 49 €, offizielles Formular kostenlos.'],
        'zahlung-aendern' => ['Zahlungsrhythmus beim Rundfunkbeitrag ändern', 'Lieber halbjährlich oder jährlich zahlen? Wir teilen dem Beitragsservice Ihren gewünschten Zahlungsrhythmus mit. Einmalig 49 €, offizielles Formular kostenlos.'],
        'zweitwohnung' => ['Nebenwohnung vom Rundfunkbeitrag befreien', 'Sie zahlen für Ihre Hauptwohnung? Wir melden die Nebenwohnung bei Bedarf an und beantragen die Befreiung. Einmalig 49 €.'],
        'weitere-wohnung-anmelden' => ['Weitere Wohnung beim Rundfunkbeitrag anmelden', 'Eine zusätzliche Wohnung? Wir zeigen, ob eine Befreiung möglich ist oder eine Anmeldung nötig ist, damit Sie nicht doppelt zahlen.'],
    ];
}

function seo_static(): array
{
    return [
        '/' => ['Rundfunkbeitrag anmelden, abmelden, ändern · Rundfunkbeitrag Support', 'Persönliche Unterstützung bei Rundfunkbeitrag-Anliegen: Abmeldung, Anmeldung, Änderung, Nebenwohnung. Unabhängiger Privatservice, einmalig 49 €. Offizielle Formulare sind kostenlos.'],
        '/kontakt' => ['Kontakt · Rundfunkbeitrag Support', 'Fragen zu Ihrem Rundfunkbeitrag-Anliegen oder Ihrem Vorgang? Schreiben Sie uns.'],
        '/impressum' => ['Impressum · Rundfunkbeitrag Support', null], '/datenschutz' => ['Datenschutz · Rundfunkbeitrag Support', null], '/agb' => ['AGB · Rundfunkbeitrag Support', null], '/widerruf' => ['Widerruf · Rundfunkbeitrag Support', null],
        '/weitere-anliegen' => ['Weitere Rundfunkbeitrag-Anliegen · Rundfunkbeitrag Support', null],
    ];
}
// Returns the article for /ratgeber/<slug> (de) or /guide/<slug> (en), or null.
function seo_article(string $path): ?array
{
    if (!preg_match('#^/(ratgeber|guide)/([a-z0-9-]+)$#', $path, $m)) {
        return null;
    }
    $lang = $m[1] === 'guide' ? 'en' : 'de';
    foreach (seo_articles() as $a) {
        if ($a['slug'] === $m[2] && $a['lang'] === $lang) {
            return $a;
        }
    }
    return null;
}

function seo_article_url(array $a): string
{
    return ($a['lang'] === 'en' ? '/guide/' : '/ratgeber/').$a['slug'];
}

function seo_known(string $path): bool
{
    return $path === '/ratgeber' || $path === '/guide' || seo_article($path) !== null || seo_service_path($path) !== null;
}
// Title, description, page language, server-rendered guide HTML and hreflang alternates for a path.
function seo_meta(string $path): array
{
    $base = config()['public_url'];
    $t = 'Rundfunkbeitrag Support';
    $m = ['title' => $t, 'description' => 'Persönliche administrative Unterstützung für Rundfunkbeitrag-Anliegen. Unabhängiger Privatservice. Einmalig 49 €.', 'lang' => null, 'ssr' => '', 'alternates' => []];
    $s = seo_static()[$path] ?? null;
    if ($s) {
        $m['title'] = $s[0];
        if ($s[1]) {
            $m['description'] = $s[1];
        }
        if ($path === '/') {
            $m['ssr'] = seo_home_html();
        }
        return $m;
    }
    if ($sv = seo_service_path($path)) {
        [$slug,$sl] = $sv;
        $d = seo_service_data()[$slug][$sl];
        $m['lang'] = $sl;
        if ($sl === 'de') {
            [$m['title'],$m['description']] = seo_services()[$slug];
        } else {
            $m['title'] = $d['title'].': we handle your Rundfunkbeitrag request';
            $m['description'] = $d['lead'];
        }
        $m['title'] .= ' · '.$t;
        $m['alternates'] = [['de', $base.seo_service_url($slug, 'de')], ['en', $base.seo_service_url($slug, 'en')]];
        $m['ssr'] = seo_service_html($slug, $sl);
        $m['jsonld'] = seo_service_jsonld($slug, $sl);
        return $m;
    }
    if (preg_match('#^/leistungen/([a-z-]+)$#', $path, $x) && isset(seo_services()[$x[1]])) {
        [$m['title'],$m['description']] = seo_services()[$x[1]];
        $m['title'] .= ' · '.$t;
        return $m;
    }
    if ($path === '/ratgeber' || $path === '/guide') {
        $en = $path === '/guide';
        $m['lang'] = $en ? 'en' : 'de';
        $m['title'] = ($en ? 'Guides: the German broadcasting fee explained' : 'Ratgeber zum Rundfunkbeitrag').' · '.$t;
        $m['description'] = $en ? 'Plain-English guides to the Rundfunkbeitrag: leaving Germany, moving in together, second homes, first flat and deregistering after a death.' : 'Verständliche Ratgeber zum Rundfunkbeitrag: Abmeldung nach Todesfall, Umzug ins Ausland, Zusammenziehen, Nebenwohnung und erste Wohnung.';
        $m['alternates'] = [['de', $base.'/ratgeber'], ['en', $base.'/guide']];
        $items = '';
        foreach (seo_articles() as $a) {
            if ($a['lang'] === $m['lang']) {
                $items .= '<li><a href="'.h(seo_article_url($a)).'">'.h($a['title']).'</a><p>'.h($a['description']).'</p></li>';
            }
        }
        $m['ssr'] = '<article class="rb-article" lang="'.$m['lang'].'"><p class="eyebrow">'.($en ? 'Guides' : 'Ratgeber').'</p><h1>'.($en ? 'The German broadcasting fee, explained' : 'Rundfunkbeitrag verständlich erklärt').'</h1><p class="public-lead">'.($en ? 'What to do in common situations, and how to do it yourself for free with the official forms.' : 'Was in häufigen Situationen zu tun ist und wie Sie es mit den offiziellen Formularen kostenlos selbst erledigen.').'</p><ul class="rb-article-list">'.$items.'</ul><p class="rb-article-alt"><a href="'.($en ? '/ratgeber" hreflang="de">Ratgeber auf Deutsch' : '/guide" hreflang="en">Guides in English').'</a></p>'.seo_disclaimer($m['lang']).'</article>';
        return $m;
    }
    $a = seo_article($path);
    if (!$a) {
        return $m;
    }
    $en = $a['lang'] === 'en';
    $code = seo_codes()[$a['service']] ?? null;
    $m['lang'] = $a['lang'];
    $m['title'] = $a['title'].' · '.$t;
    $m['description'] = $a['description'];
    $alt = null;
    foreach (seo_articles() as $b) {
        if ($b['slug'] === $a['alt'] && $b['lang'] !== $a['lang']) {
            $alt = $b;
        }
    }
    if ($alt) {
        $m['alternates'] = [[$a['lang'], $base.$path], [$alt['lang'], $base.seo_article_url($alt)]];
    }
    $cta = '<aside class="rb-article-cta"><h2>'.($en ? 'Rather have it done for you?' : 'Lieber erledigen lassen?').'</h2><p>'.($en ? 'We check your details and documents, submit your request with your authorisation and keep you informed at every step. One-off €49, no subscription.' : 'Wir prüfen Ihre Angaben und Nachweise, reichen Ihren Antrag mit Ihrer Vollmacht ein und informieren Sie bei jedem Schritt. Einmalig 49 €, kein Abonnement.').'</p><p>'.($code ? '<a class="public-button" href="/auftrag/'.strtolower($code).'">'.($en ? 'Start your request' : 'Anliegen starten').'</a> ' : '').'<a href="/leistungen/'.h($a['service']).'">'.($en ? 'Documents and process' : 'Unterlagen und Ablauf').'</a></p></aside>';
    $m['ssr'] = '<article class="rb-article" lang="'.$a['lang'].'"><p class="eyebrow"><a href="'.($en ? '/guide">Guides' : '/ratgeber">Ratgeber').'</a></p><h1>'.h($a['title']).'</h1>'.$a['body'].$cta.($alt ? '<p class="rb-article-alt"><a href="'.h(seo_article_url($alt)).'" hreflang="'.$alt['lang'].'">'.($en ? 'Auf Deutsch lesen' : 'Read in English').'</a></p>' : '').seo_disclaimer($a['lang']).'</article>';
    return $m;
}

function seo_disclaimer(string $lang): string
{
    return '<p class="rb-article-note">'.($lang === 'en' ? 'As of October 2026. General information, not legal advice. We are not affiliated with ARD, ZDF, Deutschlandradio or Beitragsservice. Only Beitragsservice makes the decision.' : 'Stand: Oktober 2026. Allgemeine Information, keine Rechtsberatung. Wir sind nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden. Die Entscheidung trifft ausschließlich der Beitragsservice.').'</p>';
}

function sitemap_xml(): string
{
    $base = config()['public_url'];
    $paths = ['/', '/ratgeber', '/guide', '/kontakt', '/impressum', '/datenschutz', '/agb', '/widerruf'];
    foreach (array_keys(seo_services()) as $s) {
        $paths[] = '/leistungen/'.$s;
    }
    foreach (array_keys(seo_service_data()) as $s) {
        $paths[] = seo_service_url($s, 'en');
    }
    foreach (seo_articles() as $a) {
        $paths[] = seo_article_url($a);
    }
    $x = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($paths as $p) {
        $x .= '<url><loc>'.h($base.$p).'</loc></url>'."\n";
    }
    return $x.'</urlset>'."\n";
}
// Service pages: /leistungen/<slug> (de) and /services/<en slug> (en).
function seo_service_data(): array
{
    static $s = null;
    return $s ??= require __DIR__.'/services.php';
}

function seo_service_url(string $slug, string $lang): string
{
    return $lang === 'en' ? '/services/'.seo_service_data()[$slug]['enslug'] : '/leistungen/'.$slug;
}
// Returns [slug, lang] for a service page path, or null.
function seo_service_path(string $path): ?array
{
    if (preg_match('#^/leistungen/([a-z-]+)$#', $path, $m) && isset(seo_service_data()[$m[1]])) {
        return [$m[1], 'de'];
    }
    if (preg_match('#^/services/([a-z-]+)$#', $path, $m)) {
        foreach (seo_service_data() as $slug => $s) {
            if ($s['enslug'] === $m[1]) {
                return [$slug, 'en'];
            }
        }
    }
    return null;
}

function seo_service_html(string $slug, string $lang): string
{
    $s = seo_service_data()[$slug];
    $d = $s[$lang];
    $en = $lang === 'en';
    $start = '/auftrag/'.strtolower($s['code']);
    $L = fn ($de, $e) => $en ? $e : $de;
    $li = fn (array $a) => implode('', array_map(fn ($x) => '<li>'.h($x).'</li>', $a));
    $no = '';
    foreach ($d['no'] as [$t,$o]) {
        $no .= '<li>'.h($t).($o ? ' <a href="'.h(seo_service_url($o, $lang)).'">'.h(seo_service_data()[$o][$lang]['title']).'</a>' : '').'</li>';
    }
    $faq = array_merge($d['faq'], [[$L('Warum kostet das 49 €, wenn das offizielle Formular kostenlos ist?', 'Why does this cost €49 when the official form is free?'), $L('Die offiziellen Formulare des Beitragsservice sind kostenlos, und Sie können sie selbst nutzen. Wir sind ein unabhängiger, kostenpflichtiger Service: Wir prüfen Ihre Angaben und Nachweise persönlich, reichen Ihren Antrag mit Ihrer Vollmacht ein und halten Sie bei jedem Schritt auf dem Laufenden.', 'The official Beitragsservice forms are free and you can use them yourself. We are an independent, paid service: we check your details and documents personally, submit your request with your authorisation and keep you informed at every step.')]]);
    $fq = '';
    foreach ($faq as [$q,$a]) {
        $fq .= '<details><summary>'.h($q).'</summary><p>'.h($a).'</p></details>';
    }
    $timing = [[$L('Ihre Angaben', 'Your details'), $L('etwa 2–5 Minuten', 'about 2–5 minutes')], [$L('Auftragsbestätigung', 'Order confirmation'), $L('sofort per E-Mail (noch keine Einreichung)', 'straight away by email (not a submission yet)')], [$L('Einreichung', 'Submission'), $L('spätestens 3 Geschäftstage nach vollständigen Unterlagen; wir informieren Sie per E-Mail', 'within 3 business days once your documents are complete; we let you know by email')], [$L('Antwort des Beitragsservice', 'Reply from Beitragsservice'), $L('in der Regel nach einigen Wochen, per Post direkt an Sie', 'usually after a few weeks, by post directly to you')]];
    $tm = '';
    foreach ($timing as $i => [$a,$b]) {
        $tm .= '<div class="rb-timing-item"><span class="rb-timing-dot" aria-hidden="true">'.($i + 1).'</span><p><strong>'.h($a).'</strong><span>'.h($b).'</span></p></div>';
    }
    $steps = [[$L('Angaben machen', 'Enter your details'), $L('Sie beantworten die Fragen zu Ihrer Situation und laden bei Bedarf Nachweise hoch.', 'You answer the questions about your situation and upload documents if needed.')], [$L('Prüfen und bevollmächtigen', 'Review and authorise'), $L('Sie prüfen alles, erteilen die Vollmacht und erhalten sofort die Auftragsbestätigung per E-Mail. Sie bestätigt Ihren Auftrag bei uns, nicht schon die Einreichung beim Beitragsservice.', 'You check everything, give your authorisation and get the order confirmation by email straight away. It confirms your order with us, not yet the submission to Beitragsservice.')], [$L('Wir reichen ein', 'We submit'), $L('Wir prüfen Ihre Angaben, fordern Fehlendes an und reichen Ihren Antrag innerhalb von drei Geschäftstagen nach Vorliegen aller Unterlagen in Ihrem Namen ein. Sie zahlen nach Erhalt der Auftragsbestätigung per Überweisung'.(stripe_public() ? ' oder online (zum Beispiel mit Karte)' : '').'; mit der Bearbeitung warten wir nicht auf Ihre Zahlung. Geschäftstage sind Montag bis Freitag, außer an gesetzlichen Feiertagen an unserem Sitz in Litauen.', 'We check your details, request anything missing and submit on your behalf within three business days once everything is complete. You pay by bank transfer'.(stripe_public() ? ' or online (for example by card)' : '').' once you receive the order confirmation; we do not wait for your payment before starting work. Business days are Monday to Friday, excluding public holidays at our registered office in Lithuania.')]];
    $st = '';
    foreach ($steps as [$a,$b]) {
        $st .= '<li><strong>'.h($a).'</strong><span>'.h($b).'</span></li>';
    }
    $get = [$L('Bestellbestätigung mit Auftragsnummer per E-Mail', 'Order confirmation with your order number by email'), $L('Ihren persönlichen Vorgang mit dem aktuellen Stand online', 'Your personal case page with its current status'), $L('Eine E-Mail bei jedem wichtigen Schritt, auch nach der Einreichung', 'An email at every important step, including when we have submitted'), $L('Nachrichten an Ihre Sachbearbeitung direkt im Vorgang', 'Messages to your case worker right in your case')];
    $guide = null;
    foreach (seo_articles() as $a) {
        if ($a['service'] === $slug && $a['lang'] === $lang) {
            $guide = $a;
        }
    }
    $alt = seo_service_url($slug, $en ? 'de' : 'en');
    return '<article class="rb-service" lang="'.$lang.'">'
    .'<nav class="rb-crumbs" aria-label="'.$L('Brotkrumen', 'Breadcrumb').'"><a href="/">'.$L('Startseite', 'Home').'</a><span aria-hidden="true">/</span><a href="/#leistungen">'.$L('Leistungen', 'Services').'</a></nav>'
    .'<header class="rb-svc-hero"><div class="rb-svc-intro"><img class="rb-svc-icon" src="/icons/'.$s['icon'].'.webp" width="64" height="64" alt=""><h1>'.h($d['title']).'</h1><p class="public-lead">'.h($d['lead']).'</p>'
    .'<ul class="rb-svc-facts"><li>'.$L('Einmalig 49 €, kein Abonnement', 'One-off €49, no subscription').'</li><li>'.$L('Angaben in etwa 2–5 Minuten', 'Details in about 2–5 minutes').'</li><li>'.$L('Persönliche Prüfung vor der Einreichung', 'Personal check before submission').'</li></ul></div>'
    .'<aside class="rb-svc-price"><p class="rb-svc-price-label">'.$L('Einmalige Servicegebühr', 'One-off service fee').'</p><p class="rb-svc-amount">'.$L('49 €', '€49').'</p><p>'.$L('Prüfung, Vorbereitung und Einreichung mit Ihrer Vollmacht.', 'Checking, preparing and submitting your request with your authorisation.').'</p><a class="public-button" href="'.$start.'">'.$L('Anliegen starten', 'Start your request').'</a><p class="rb-svc-small">'.$L('Unabhängiger Service. Die offiziellen Formulare sind kostenlos.', 'Independent service. The official forms are free.').'</p></aside></header>'
    .'<section class="rb-svc-fit"><div class="rb-fit-yes"><h2>'.$L('Passt, wenn', 'Right for you if').'</h2><ul>'.$li($d['yes']).'</ul></div><div class="rb-fit-no"><h2>'.$L('Passt nicht, wenn', 'Not right if').'</h2><ul>'.$no.'</ul></div></section>'
    .'<section class="rb-svc-block"><h2>'.$L('Das benötigen Sie', 'What you need').'</h2><ul class="rb-svc-check">'.$li($d['needed']).'</ul><p class="rb-svc-note">'.h($d['note']).'</p></section>'
    .'<section class="rb-svc-block"><h2>'.$L('So läuft es ab', 'How it works').'</h2><ol class="rb-svc-steps">'.$st.'</ol></section>'
    .'<section class="rb-svc-block"><h2>'.$L('Wie lange es dauert', 'How long it takes').'</h2><div class="rb-timing">'.$tm.'</div></section>'
    .'<section class="rb-svc-block"><h2>'.$L('Das bekommen Sie', 'What you get').'</h2><ul class="rb-svc-check">'.$li($get).'</ul></section>'
    .'<section class="rb-svc-block rb-svc-faq"><h2>'.$L('Häufige Fragen', 'Frequently asked questions').'</h2>'.$fq.'</section>'
    .($guide ? '<p class="rb-svc-guide"><a href="'.h(seo_article_url($guide)).'">'.$L('Mehr dazu im Ratgeber: ', 'Read our guide: ').h($guide['title']).'</a></p>' : '')
    .'<aside class="rb-article-cta"><h2>'.$L('Bereit? Wir übernehmen das.', 'Ready? We handle it.').'</h2><p>'.$L('Einmalig 49 €. Die Entscheidung trifft ausschließlich der Beitragsservice; Einreichung und Entscheidung sind getrennte Schritte.', 'One-off €49. Only Beitragsservice makes the decision; submission and decision are separate steps.').'</p><p><a class="public-button" href="'.$start.'">'.$L('Anliegen starten', 'Start your request').'</a></p></aside>'
    .'<p class="rb-article-alt"><a href="'.h($alt).'" hreflang="'.($en ? 'de' : 'en').'">'.($en ? 'Auf Deutsch lesen' : 'Read in English').'</a></p>'.seo_disclaimer($lang).'</article>';
}

function seo_service_jsonld(string $slug, string $lang): array
{
    $s = seo_service_data()[$slug];
    $d = $s[$lang];
    $base = config()['public_url'];
    $en = $lang === 'en';
    $url = $base.seo_service_url($slug, $lang);
    $faq = [];
    foreach ($d['faq'] as [$q,$a]) {
        $faq[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
    }
    return [['@context' => 'https://schema.org', '@type' => 'Service', 'name' => $d['title'], 'description' => $d['lead'], 'url' => $url, 'areaServed' => 'DE', 'inLanguage' => $lang, 'provider' => ['@type' => 'Organization', 'name' => 'Rundfunkbeitrag Support', 'url' => $base.'/'], 'offers' => ['@type' => 'Offer', 'price' => '49.00', 'priceCurrency' => 'EUR', 'url' => $base.'/auftrag/'.strtolower($s['code'])]],
        ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => $en ? 'Home' : 'Startseite', 'item' => $base.'/'], ['@type' => 'ListItem', 'position' => 2, 'name' => $en ? 'Services' : 'Leistungen', 'item' => $base.'/#leistungen'], ['@type' => 'ListItem', 'position' => 3, 'name' => $d['title'], 'item' => $url]]],
        ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq]];
}
// Link preview (Open Graph) and structured data tags for the page head.
function seo_head(string $path, array $seo): string
{
    $base = config()['public_url'];
    $lang = $seo['lang'] ?? 'de';
    $o = '<meta property="og:type" content="website"><meta property="og:site_name" content="Rundfunkbeitrag Support"><meta property="og:title" content="'.h(preg_replace('/ · Rundfunkbeitrag Support$/', '', $seo['title'])).'"><meta property="og:description" content="'.h($seo['description']).'"><meta property="og:url" content="'.h($base.$path).'"><meta property="og:image" content="'.h($base.'/brand/og-49.png').'"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta property="og:locale" content="'.($lang === 'en' ? 'en_GB' : 'de_DE').'"><meta name="twitter:card" content="summary_large_image">';
    $ld = $seo['jsonld'] ?? [];
    if ($path === '/') {
        $ld[] = ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'Rundfunkbeitrag Support', 'url' => $base.'/', 'logo' => $base.'/brand/antenna.svg', 'description' => 'Unabhängiger, kostenpflichtiger Privatservice für Rundfunkbeitrag-Anliegen. Nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden.'];
    }
    if ($a = seo_article($path)) {
        $ld[] = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $a['title'], 'description' => $a['description'], 'inLanguage' => $a['lang'], 'dateModified' => '2026-10-24', 'author' => ['@type' => 'Organization', 'name' => 'Rundfunkbeitrag Support'], 'publisher' => ['@type' => 'Organization', 'name' => 'Rundfunkbeitrag Support'], 'mainEntityOfPage' => $base.$path];
    }
    foreach ($ld as $x) {
        $o .= '<script type="application/ld+json">'.json_encode($x, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG).'</script>';
    }
    return $o;
}
// Server-rendered homepage summary for crawlers and link previews; the app replaces it on load.
function seo_home_html(): string
{
    $li = '';
    foreach (seo_service_data() as $slug => $x) {
        $li .= '<li><a href="/leistungen/'.h($slug).'">'.h($x['de']['title']).'</a></li>';
    }
    return '<div class="rb-ssr-home"><h1>Rundfunkbeitrag ummelden, abmelden, anmelden. Wir übernehmen das.</h1><p>Sie wählen Ihr Anliegen und machen Ihre Angaben. Wir prüfen alles persönlich und reichen Ihren Antrag mit Ihrer Vollmacht beim Beitragsservice ein. Einmalig 49 €, kein Abonnement.</p><h2>Unsere Leistungen</h2><ul>'.$li.'</ul><p><a href="/antrag">Anliegen starten</a> · <a href="/kontakt">Kontakt</a> · <a href="/services/moving-or-name-change" hreflang="en">English</a></p><p>Unabhängiger, kostenpflichtiger Privatservice. Wir sind nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden. Die offiziellen Formulare sind kostenlos.</p></div>';
}
