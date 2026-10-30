<?php

declare(strict_types=1);
// Texts of the customer emails and the order PDF, one block per language. Each customer gets them only in the language
// chosen on the site (stored as order.language at order time). To add a language, add a block with the same keys;
// a key missing in a language falls back to English, then German. Staff emails are not translated here.
const RB_TEXT = [
    'de' => [
        'inv.file' => 'Rechnung', 'inv.title' => 'Rechnung', 'inv.number' => 'Rechnungsnummer', 'inv.date' => 'Rechnungsdatum', 'inv.serviceDate' => 'Leistungsdatum', 'inv.billedTo' => 'Rechnungsempfänger',
        'inv.item' => 'Beschreibung', 'inv.qty' => 'Menge', 'inv.amount' => 'Betrag', 'inv.itemText' => 'Servicegebühr: {service}', 'inv.itemRef' => 'Auftrag {ref}', 'inv.net' => 'Nettobetrag', 'inv.vat' => 'Umsatzsteuer {rate} %', 'inv.total' => 'Gesamtbetrag',
        'inv.paid' => 'Bezahlt am {date} per Überweisung. Vielen Dank!', 'inv.paidNoDate' => 'Bezahlt per Überweisung. Vielen Dank!', 'inv.mail' => 'Ihre Rechnung finden Sie im Anhang (PDF).',
        'money' => '{a} €', 'dec' => ',', 'thou' => '.',
        'title.order' => 'Bestätigung Ihres Serviceauftrags', 'title.request-information' => 'Rückfrage', 'title.record-submission' => 'Einreichung dokumentiert', 'title.record-response' => 'Offizielle Antwort verfügbar', 'title.withdraw' => 'Widerruf eingegangen', 'title.payment-received' => 'Zahlung eingegangen', 'title.complaint' => 'Nachricht erhalten', 'title.privacy-request' => 'Datenschutzanfrage erhalten', 'title.update' => 'Aktualisierung',
        'step.payment-received' => ['Ihre Zahlung ist bei uns eingegangen. Vielen Dank! Den aktuellen Stand Ihres Auftrags sehen Sie jederzeit in Ihrem Vorgang.'],
        'step.request-information' => ['Wir benötigen noch Angaben oder Unterlagen von Ihnen. Die Rückfrage finden Sie in Ihrem Vorgang. Bis zu Ihrer Antwort ruht die Bearbeitung.'],
        'order.debit' => 'Lastschrift: Das SEPA-Lastschriftmandat für den Rundfunkbeitrag kann nur die Kontoinhaberin oder der Kontoinhaber selbst erteilen; wir dürfen das nicht für Sie tun. Wir melden die Zahlung deshalb zunächst per Überweisung an. So stellen Sie danach selbst auf Lastschrift um: Sobald Sie Post vom Beitragsservice mit Ihrer Beitragsnummer haben, öffnen Sie auf rundfunkbeitrag.de das Formular „Zahlungsweise ändern“, geben Ihre Beitragsnummer und Ihre IBAN ein und erteilen dort das Mandat. Das dauert etwa 3 Minuten und ist kostenlos.', 'order.remainingOther' => 'Wichtig für die Person, die weiterhin in der Wohnung lebt ({name}): Sie muss die Wohnung selbst auf ihren Namen anmelden, weil wir sie ohne ihre Vollmacht nicht vertreten dürfen. Das geht online auf rundfunkbeitrag.de über das Formular „Wohnung anmelden“ und dauert etwa 5 Minuten. Alternativ kann sie unseren Service „Erstmals anmelden“ selbst beauftragen. Die Beitragsnummer der verstorbenen Person ist nicht übertragbar.', 'submission.when' => 'Eingereicht am {date} um {time} Uhr (deutsche Zeit) beim ARD ZDF Deutschlandradio Beitragsservice.', 'step.record-submission' => ['Wir haben Ihr Anliegen in Ihrem Namen beim Beitragsservice eingereicht. Der Beleg liegt in Ihrem Vorgang.', 'Die Entscheidung trifft der Beitragsservice. Er antwortet in der Regel per Post direkt an Sie; das kann einige Wochen dauern.', 'Bitte schicken Sie uns ein Foto oder einen Scan dieses Schreibens: Antworten Sie einfach auf diese E-Mail. Dann prüfen wir, ob alles erledigt ist, und schließen Ihren Vorgang ab.'],
        'step.complaint' => ['Vielen Dank für Ihre Nachricht. Wir berücksichtigen sie bei Ihrem Auftrag und melden uns bei Bedarf per E-Mail.'],
        'step.privacy-request' => ['Wir haben Ihre Datenschutzanfrage erhalten und antworten Ihnen per E-Mail.'],
        'step.record-response' => ['Die Antwort des Beitragsservice finden Sie in Ihrem Vorgang. Bitte sehen Sie sie sich an.'], 'step.submittedNoReceipt' => 'Wir haben Ihr Anliegen in Ihrem Namen beim Beitragsservice eingereicht.',
        'case.linkIntro' => 'Ihr Vorgang (persönlicher Link, 14 Tage gültig, bitte nicht weitergeben):', 'case.linkNote' => 'Persönlicher Link, 14 Tage gültig. Bitte nicht weitergeben.',
        'access.title' => 'Privater Zugang', 'access.body' => 'Mit diesem Link öffnen Sie Ihren Vorgang. Der Link kann nur einmal verwendet werden und ersetzt den bisherigen Zugang in Ihrem Browser.', 'access.note' => 'Der Link ist 30 Minuten gültig.',
        'verify.subject' => 'Ihr Bestätigungscode: {code}', 'verify.title' => 'Ihr Bestätigungscode', 'verify.preheader' => 'Bestätigungscode {code}', 'verify.use' => 'Geben Sie diesen Code im Bestellformular ein. Er ist 30 Minuten gültig.', 'verify.ignore' => 'Wenn Sie keinen Auftrag begonnen haben, können Sie diese E-Mail ignorieren.',
        'pay.title' => 'Ihr Auftrag ist in Bearbeitung', 'pay.preheader' => 'Zahlungsangaben und Auftragsbestätigung', 'pay.intro' => 'Wir haben mit der Bearbeitung Ihres Auftrags begonnen und warten auf Ihre Zahlung von {amount}.',
        'pay.transfer' => 'Bitte überweisen Sie', 'pay.recipient' => 'Empfänger', 'pay.amount' => 'Betrag', 'pay.reference' => 'Verwendungszweck', 'pay.referenceNote' => 'Bitte geben Sie bei der Überweisung unbedingt den Verwendungszweck an, damit wir Ihre Zahlung zuordnen können.',
        'pay.card' => 'Sie können auch online mit Karte, Apple Pay oder Google Pay bezahlen: über den Link zu Ihrem Vorgang unten.', 'pay.girocode' => 'GiroCode: Mit Ihrer Banking-App scannen, alle Angaben werden übernommen.', 'pay.girocodeText' => 'GiroCode zum Scannen mit Ihrer Banking-App: siehe Anhang und Ihre Vorgangsseite.',
        'pay.yourOrder' => 'Ihr Auftrag', 'pay.orderNumber' => 'Auftragsnummer', 'pay.service' => 'Leistung', 'pay.orderedOn' => 'Bestellt am', 'pay.pdf' => 'Ihre Auftragsbestätigung mit allen Angaben und der Vollmacht finden Sie im Anhang (PDF).',
        'rem.subject' => 'Zahlungserinnerung', 'rem.title' => 'Ihre Zahlung ist noch nicht eingegangen', 'rem.preheader' => 'Zahlungsangaben zu Ihrem Auftrag',
        'rem.intro' => 'Vielen Dank für Ihren Auftrag vom {date}. Bis heute haben wir Ihre Zahlung von {amount} noch nicht erhalten. Ihren Auftrag bearbeiten wir unabhängig davon weiter.',
        'rem.intro2' => 'Vielen Dank für Ihren Auftrag vom {date}. Ihre Zahlung von {amount} ist bei uns noch nicht eingegangen. Wir möchten Sie daher freundlich daran erinnern.',
        'rem.subjectLast' => 'Letzte Zahlungserinnerung', 'rem.titleLast' => 'Unsere letzte Erinnerung an Ihre Zahlung',
        'rem.introLast' => 'Vielen Dank für Ihren Auftrag vom {date}. Ihre Zahlung von {amount} ist bei uns noch nicht eingegangen. Dies ist unsere letzte Erinnerung; danach schreiben wir Ihnen zu dieser Zahlung nicht mehr. Wir freuen uns, wenn Sie den Betrag noch überweisen.',
        'rem.paid' => 'Haben Sie bereits überwiesen? Dann ist diese E-Mail gegenstandslos; Überweisungen brauchen manchmal 1–2 Bankarbeitstage.', 'rem.free' => 'Diese Erinnerung ist für Sie kostenfrei. Fragen? Antworten Sie einfach auf diese E-Mail.',
        'mail.order' => 'Auftrag', 'mail.open' => 'Vorgang öffnen', 'mail.private' => 'Wir sind ein privater Dienstleister und nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden.', 'mail.auto' => 'Diese E-Mail wurde automatisch versendet.',
        'pdf.file' => 'Auftragsbestaetigung', 'pdf.confirmation' => 'Auftragsbestätigung', 'pdf.authority' => 'Vollmacht', 'pdf.date' => 'Datum', 'pdf.price' => 'Preis', 'pdf.page' => 'Seite', 'pdf.provider' => 'Anbieter', 'pdf.customer' => 'Auftraggeber/in',
        'pdf.payment' => 'Zahlung per Überweisung', 'pdf.referenceNote' => 'Bitte geben Sie den Verwendungszweck unbedingt an.', 'pdf.girocode' => 'GiroCode: mit der Banking-App scannen',
        'pdf.data' => 'Ihre Angaben', 'pdf.declarations' => 'Erklärungen', 'pdf.accepted' => 'Zugestimmt', 'pdf.notRequested' => 'Nicht verlangt', 'pdf.terms' => 'Servicebedingungen', 'pdf.withdrawal' => 'Widerrufsbelehrung', 'pdf.privacy' => 'Datenschutz',
        'pdf.place' => 'Ort, Datum', 'pdf.signature' => 'Unterschrift', 'pdf.note' => 'Wir sind ein privater Dienstleister und nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden. Auftragseingang, Einreichung des Antrags und die behördliche Entscheidung sind getrennte Schritte.',
    ],
    'en' => [
        'inv.file' => 'invoice', 'inv.title' => 'Invoice', 'inv.number' => 'Invoice number', 'inv.date' => 'Invoice date', 'inv.serviceDate' => 'Service date', 'inv.billedTo' => 'Billed to',
        'inv.item' => 'Description', 'inv.qty' => 'Qty', 'inv.amount' => 'Amount', 'inv.itemText' => 'Service fee: {service}', 'inv.itemRef' => 'Order {ref}', 'inv.net' => 'Net amount', 'inv.vat' => 'VAT {rate} %', 'inv.total' => 'Total',
        'inv.paid' => 'Paid on {date} by bank transfer. Thank you!', 'inv.paidNoDate' => 'Paid by bank transfer. Thank you!', 'inv.mail' => 'Your invoice is attached (PDF).',
        'money' => '€{a}', 'dec' => '.', 'thou' => ',',
        'title.order' => 'Your service order confirmation', 'title.request-information' => 'More information needed', 'title.record-submission' => 'Submission recorded', 'title.record-response' => 'Official response available', 'title.withdraw' => 'Withdrawal received', 'title.payment-received' => 'Payment received', 'title.complaint' => 'Message received', 'title.privacy-request' => 'Data protection request received', 'title.update' => 'Case update',
        'step.payment-received' => ['We have received your payment. Thank you! You can see the current status of your order at any time in your case.'],
        'step.request-information' => ['We need some more information or documents from you. You will find our question in your case. We will continue as soon as you reply.'],
        'order.debit' => 'Direct debit: only the account holder can grant the SEPA direct debit mandate for the Rundfunkbeitrag; we may not do this for you. We therefore register payment by bank transfer first. To switch to direct debit afterwards: once you receive a letter from Beitragsservice with your contribution number, open the form for changing the payment method (“Zahlungsweise ändern”) on rundfunkbeitrag.de, enter your contribution number and IBAN, and grant the mandate there. It takes about 3 minutes and is free of charge.', 'order.remainingOther' => 'Important for the person who still lives in the home ({name}): they must register the home in their own name themselves, because we may not act for them without their authorisation. This can be done online at rundfunkbeitrag.de with the form for registering a home (“Wohnung anmelden”) and takes about 5 minutes. Alternatively, they can order our “First registration” service themselves. The deceased person’s contribution number cannot be transferred.', 'submission.when' => 'Submitted on {date} at {time} (German time) to ARD ZDF Deutschlandradio Beitragsservice.', 'step.record-submission' => ['We have submitted your request to Beitragsservice in your name. The receipt is in your case.', 'Beitragsservice makes the decision. It usually replies by post directly to you; this can take a few weeks.', 'Please send us a photo or scan of that letter: simply reply to this email. We will then check that everything is done and close your case.'],
        'step.complaint' => ['Thank you for your message. We will take it into account in processing your order and contact you by email if needed.'],
        'step.privacy-request' => ['We have received your data protection request and will reply by email.'],
        'step.record-response' => ['You will find the reply from Beitragsservice in your case. Please take a look at it.'], 'step.submittedNoReceipt' => 'We have submitted your request to Beitragsservice in your name.',
        'case.linkIntro' => 'Your case (personal link, valid for 14 days, please do not share):', 'case.linkNote' => 'Personal link, valid for 14 days. Please do not share it.',
        'access.title' => 'Private access', 'access.body' => 'Use this link to open your case. It can be used only once and replaces your previous access in your browser.', 'access.note' => 'The link is valid for 30 minutes.',
        'verify.subject' => 'Your verification code: {code}', 'verify.title' => 'Your verification code', 'verify.preheader' => 'Verification code {code}', 'verify.use' => 'Enter this code in the order form. It is valid for 30 minutes.', 'verify.ignore' => 'If you did not start an order, you can ignore this email.',
        'pay.title' => 'Your order is in progress', 'pay.preheader' => 'Payment details and order confirmation', 'pay.intro' => 'We have started working on your order and are waiting for your payment of {amount}.',
        'pay.transfer' => 'Please transfer', 'pay.recipient' => 'Recipient', 'pay.amount' => 'Amount', 'pay.reference' => 'Payment reference', 'pay.referenceNote' => 'Please make sure to include the payment reference so that we can match your payment.',
        'pay.card' => 'You can also pay online by card, Apple Pay or Google Pay: use the link to your case below.', 'pay.girocode' => 'GiroCode: scan it with your banking app to fill in the transfer automatically.', 'pay.girocodeText' => 'GiroCode to scan with your banking app: see the attachment and your case page.',
        'pay.yourOrder' => 'Your order', 'pay.orderNumber' => 'Order number', 'pay.service' => 'Service', 'pay.orderedOn' => 'Ordered on', 'pay.pdf' => 'Your order confirmation with all details and the power of attorney is attached (PDF).',
        'rem.subject' => 'Payment reminder', 'rem.title' => 'We have not received your payment yet', 'rem.preheader' => 'Payment details for your order',
        'rem.intro' => 'Thank you for your order of {date}. We have not yet received your payment of {amount}. We are continuing to work on your order regardless.',
        'rem.intro2' => 'Thank you for your order of {date}. We have not yet received your payment of {amount}, so we would like to send you a friendly reminder.',
        'rem.subjectLast' => 'Final payment reminder', 'rem.titleLast' => 'Our final reminder about your payment',
        'rem.introLast' => 'Thank you for your order of {date}. We have not yet received your payment of {amount}. This is our final reminder; we will not write to you about this payment again. We would appreciate it if you could still transfer the amount.',
        'rem.paid' => 'Already paid? Then please disregard this email; bank transfers sometimes take 1–2 banking days.', 'rem.free' => 'This reminder is free of charge. Questions? Simply reply to this email.',
        'mail.order' => 'Order', 'mail.open' => 'Open your case', 'mail.private' => 'We are a private service provider and not affiliated with ARD, ZDF, Deutschlandradio or Beitragsservice.', 'mail.auto' => 'This email was sent automatically.',
        'pdf.file' => 'confirmation', 'pdf.confirmation' => 'Order confirmation', 'pdf.authority' => 'Power of attorney', 'pdf.date' => 'Date', 'pdf.price' => 'Price', 'pdf.page' => 'Page', 'pdf.provider' => 'Provider', 'pdf.customer' => 'Customer',
        'pdf.payment' => 'Payment by bank transfer', 'pdf.referenceNote' => 'Please make sure to include the payment reference.', 'pdf.girocode' => 'GiroCode: scan with your banking app',
        'pdf.data' => 'Your information', 'pdf.declarations' => 'Declarations', 'pdf.accepted' => 'Accepted', 'pdf.notRequested' => 'Not requested', 'pdf.terms' => 'Service terms', 'pdf.withdrawal' => 'Withdrawal information', 'pdf.privacy' => 'Privacy notice',
        'pdf.place' => 'Place, date', 'pdf.signature' => 'Signature', 'pdf.note' => 'We are a private service provider and not affiliated with ARD, ZDF, Deutschlandradio or Beitragsservice. Receipt of the order, submission of the application and the official decision are separate steps.',
    ],
];

function rb_lang(mixed $lang): string
{
    return is_string($lang) && isset(RB_TEXT[$lang]) ? $lang : 'de';
}
// The language the visitor currently sees: sent by the site script with every API call, the language cookie otherwise.
function request_lang(): string
{
    return rb_lang($_SERVER['HTTP_X_SITE_LANGUAGE'] ?? $_COOKIE['site-language'] ?? 'de');
}

function mt(string $key, string $lang, array $vars = []): string|array
{
    $v = RB_TEXT[$lang][$key] ?? RB_TEXT['en'][$key] ?? RB_TEXT['de'][$key] ?? $key;
    return is_string($v) && $vars ? strtr($v, array_combine(array_map(fn ($k) => '{'.$k.'}', array_keys($vars)), array_values($vars))) : $v;
}
// Picks one language from a ['de'=>…,'en'=>…] label (rules.json titles and field labels).
function pick_lang(array $labels, string $lang): string
{
    return (string)($labels[$lang] ?? $labels['en'] ?? $labels['de'] ?? reset($labels));
}

function mail_money(int $cents, string $lang): string
{
    return mt('money', $lang, ['a' => number_format($cents / 100, 2, mt('dec', $lang), mt('thou', $lang))]);
}
