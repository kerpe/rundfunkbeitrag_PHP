<?php

// Guide pages that send a visitor to the right service: /leistungen/<slug> (German) and /services/<enslug> (English).
// Rendered server-side by seo.php (seo_hub_html). Not linked from menus or the homepage; reached from ads, search and the sitemap.
// Each hub: enslug, per language: title, description (meta), lead, ask (heading above the cards), cards ([service slug, title, text]), note ([heading, text]), faq ([question, answer]).
// Official sources are named as text only; we do not link customers to rundfunkbeitrag.de.
return [
    'abmelden' => ['enslug' => 'deregister',
        'de' => ['title' => 'Rundfunkbeitrag abmelden',
            'seo' => 'Rundfunkbeitrag abmelden: Grund wählen, wir erledigen den Rest',
            'description' => 'Rundfunkbeitrag abmelden nach Todesfall, beim Umzug ins Ausland, beim Zusammenziehen oder bei mehreren Wohnungen. Wir reichen die Abmeldung für Sie ein. Einmalig 35 €.',
            'lead' => 'Wählen Sie Ihren Grund. Wir bereiten die Abmeldung vor und reichen sie mit Ihrer Vollmacht beim Beitragsservice ein.',
            'ask' => 'Warum möchten Sie abmelden?',
            'cards' => [
                ['ausland', 'Ich ziehe dauerhaft ins Ausland', 'Sie geben Ihre Wohnung in Deutschland ganz auf.'],
                ['zusammenziehen', 'Ich ziehe zu jemandem, der schon zahlt', 'Pro Wohnung zahlt nur eine Person. Ihr eigenes Konto wird abgemeldet.'],
                ['weitere-wohnung-aufgeben', 'Ich gebe eine von mehreren Wohnungen auf', 'Sie behalten mindestens eine Wohnung und zahlen nur noch für diese.'],
                ['daten-aendern', 'Ich ziehe nur um', 'Dann müssen Sie nicht abmelden. Ihr Konto zieht mit um, wir melden die neue Anschrift.'],
                ['sterbefall', 'Ein Angehöriger ist verstorben', 'Wir melden das Beitragskonto der verstorbenen Person ab.'],
            ],
            'note' => ['Einfach so abmelden geht nicht', 'Der Rundfunkbeitrag wird pro Wohnung gezahlt, auch ohne Fernseher oder Radio. Abmelden können Sie nur, wenn Sie für eine Wohnung nicht mehr zahlen müssen, zum Beispiel aus einem der Gründe oben. Bei geringem Einkommen kann eine Befreiung möglich sein; diesen Antrag bieten wir derzeit nicht an.'],
            'faq' => [
                ['Kann ich den Rundfunkbeitrag kündigen?', 'Eine Kündigung wie bei einem Vertrag gibt es nicht. Sie können Ihr Beitragskonto abmelden, wenn Sie für die Wohnung nicht mehr zahlen müssen, etwa nach einem Todesfall, beim Wegzug ins Ausland oder wenn Sie zu jemandem ziehen, der schon zahlt.'],
                ['Reicht die Abmeldung beim Einwohnermeldeamt?', 'Nein, darauf sollten Sie sich nicht verlassen. Das Beitragskonto läuft weiter, bis die Abmeldung beim Beitragsservice eingeht. Melden Sie es deshalb rechtzeitig ab.'],
                ['Ab wann muss ich nicht mehr zahlen?', 'Das entscheidet der Beitragsservice anhand Ihrer Angaben und Nachweise. Melden Sie deshalb möglichst bald ab. Zu viel gezahlte Beiträge werden in der Regel erstattet.'],
            ]],
        'en' => ['title' => 'Deregister from the Rundfunkbeitrag',
            'seo' => 'Deregister from the Rundfunkbeitrag: choose your reason, we handle the rest',
            'description' => 'Deregister the German broadcasting fee after a death, when leaving Germany, moving in with someone or giving up one of several homes. We submit it for you. One-off €35.',
            'lead' => 'Choose your reason. We prepare the deregistration and submit it to Beitragsservice with your authorisation.',
            'ask' => 'Why do you want to deregister?',
            'cards' => [
                ['ausland', 'I am leaving Germany for good', 'You are giving up your home in Germany completely.'],
                ['zusammenziehen', 'I am moving in with someone who already pays', 'Only one person pays per home. Your own account is deregistered.'],
                ['weitere-wohnung-aufgeben', 'I am giving up one of several homes', 'You keep at least one home and only pay for that one.'],
                ['daten-aendern', 'I am only moving home', 'Then you do not need to deregister. Your account moves with you; we report the new address.'],
                ['sterbefall', 'A relative has died', 'We deregister the contribution account of the person who died.'],
            ],
            'note' => ['You cannot simply cancel it', 'The Rundfunkbeitrag is paid per home, even without a TV or radio. You can only deregister when you no longer have to pay for a home, for example for one of the reasons above. People on a low income may be able to get an exemption; we do not offer that application at the moment.'],
            'faq' => [
                ['Can I cancel the Rundfunkbeitrag?', 'There is no cancellation like with a contract. You can deregister your contribution account when you no longer have to pay for the home, for example after a death, when leaving Germany or when you move in with someone who already pays.'],
                ['Is deregistering at the Bürgeramt enough?', 'No, do not rely on it. The contribution account keeps running until Beitragsservice receives the deregistration, so deregister in good time.'],
                ['From when do I stop paying?', 'Beitragsservice decides, based on your details and documents. So deregister as soon as you can. Overpaid contributions are usually refunded.'],
            ]]],
];
