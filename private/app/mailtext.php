<?php
declare(strict_types=1);
// Texts of the customer emails and the order PDF, one block per language. Each customer gets them only in the language
// chosen on the site (stored as order.language at order time). To add a language, add a block with the same keys;
// a key missing in a language falls back to English, then German. Staff emails are not translated here.
const RB_TEXT=[
'de'=>[
 'money'=>'{a} €','dec'=>',','thou'=>'.',
 'title.order'=>'Bestätigung Ihres Serviceauftrags','title.request-information'=>'Rückfrage','title.record-submission'=>'Einreichung dokumentiert','title.record-response'=>'Offizielle Antwort verfügbar','title.withdraw'=>'Widerruf eingegangen','title.payment-received'=>'Zahlung eingegangen','title.complaint'=>'Nachricht erhalten','title.privacy-request'=>'Datenschutzanfrage erhalten','title.update'=>'Aktualisierung',
 'step.payment-received'=>['Ihre Zahlung ist bei uns eingegangen. Vielen Dank! Den aktuellen Stand Ihres Auftrags sehen Sie jederzeit in Ihrem Vorgang.'],
 'step.request-information'=>['Wir benötigen noch etwas von Ihnen. Die Rückfrage finden Sie in Ihrem Vorgang. Bis zu Ihrer Antwort ruht die Bearbeitung.'],
 'step.record-submission'=>['Wir haben Ihr Anliegen beim Beitragsservice eingereicht. Der Beleg liegt in Ihrem Vorgang. Die Entscheidung trifft der Beitragsservice; seine Antwort kann einige Wochen dauern.','Waren Sie mit uns zufrieden? Wir freuen uns über Ihre Bewertung. Sie dauert eine Minute: Öffnen Sie Ihren Vorgang über den Link unten.'],
 'step.complaint'=>['Danke für Ihre Nachricht an die Sachbearbeitung. Wir berücksichtigen sie bei Ihrem Auftrag und melden uns, falls nötig, per E-Mail.'],
 'step.privacy-request'=>['Wir haben Ihre Datenschutzanfrage erhalten und antworten Ihnen per E-Mail.'],
 'step.record-response'=>['Die Antwort des Beitragsservice liegt in Ihrem Vorgang. Bitte prüfen Sie sie.'],
 'case.linkIntro'=>'Ihr Vorgang (persönlicher Link, 14 Tage gültig, bitte nicht weitergeben):','case.linkNote'=>'Persönlicher Link, 14 Tage gültig. Bitte nicht weitergeben.',
 'access.title'=>'Privater Zugang','access.body'=>'Öffnen Sie Ihren Vorgang. Dieser einmalige Link ersetzt den bisherigen Browserzugang.','access.note'=>'Der Link ist 30 Minuten gültig.',
 'verify.subject'=>'Ihr Bestätigungscode: {code}','verify.title'=>'Ihr Bestätigungscode','verify.preheader'=>'Bestätigungscode {code}','verify.use'=>'Geben Sie diesen Code im Bestellformular ein. Er ist 30 Minuten gültig.','verify.ignore'=>'Wenn Sie keinen Auftrag begonnen haben, können Sie diese E-Mail ignorieren.',
 'pay.title'=>'Ihr Auftrag ist in Bearbeitung','pay.preheader'=>'Zahlungsangaben und Auftragsbestätigung','pay.intro'=>'Wir haben mit der Bearbeitung Ihres Auftrags begonnen und warten auf Ihre Zahlung von {amount}.',
 'pay.transfer'=>'Bitte überweisen Sie','pay.recipient'=>'Empfänger','pay.amount'=>'Betrag','pay.reference'=>'Verwendungszweck','pay.referenceNote'=>'Bitte geben Sie bei der Überweisung unbedingt den Verwendungszweck an, damit wir Ihre Zahlung zuordnen können.',
 'pay.girocode'=>'GiroCode: mit Ihrer Banking-App scannen, alle Angaben werden übernommen.','pay.girocodeText'=>'GiroCode zum Scannen mit Ihrer Banking-App: siehe Anhang und Ihre Vorgangsseite.',
 'pay.yourOrder'=>'Ihr Auftrag','pay.orderNumber'=>'Auftragsnummer','pay.service'=>'Leistung','pay.orderedOn'=>'Bestellt am','pay.pdf'=>'Ihre Auftragsbestätigung mit allen Angaben und der Vollmacht finden Sie im Anhang (PDF).',
 'mail.order'=>'Auftrag','mail.open'=>'Vorgang öffnen','mail.private'=>'Wir sind ein privater Dienstleister und nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden.','mail.auto'=>'Diese E-Mail wurde automatisch versendet.',
 'pdf.file'=>'Auftragsbestaetigung','pdf.confirmation'=>'Auftragsbestätigung','pdf.authority'=>'Vollmacht','pdf.date'=>'Datum','pdf.price'=>'Preis','pdf.page'=>'Seite','pdf.provider'=>'Anbieter','pdf.customer'=>'Auftraggeber/in',
 'pdf.payment'=>'Zahlung per Überweisung','pdf.referenceNote'=>'Bitte geben Sie den Verwendungszweck unbedingt an.','pdf.girocode'=>'GiroCode: mit der Banking-App scannen',
 'pdf.data'=>'Ihre Angaben','pdf.declarations'=>'Erklärungen','pdf.accepted'=>'Zugestimmt','pdf.notRequested'=>'Nicht verlangt','pdf.terms'=>'Servicebedingungen','pdf.withdrawal'=>'Widerrufsbelehrung','pdf.privacy'=>'Datenschutz',
 'pdf.place'=>'Ort, Datum','pdf.signature'=>'Unterschrift','pdf.note'=>'Wir sind ein privater Dienstleister und nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden. Auftragseingang, Einreichung des Antrags und die behördliche Entscheidung sind getrennte Schritte.',
],
'en'=>[
 'money'=>'€{a}','dec'=>'.','thou'=>',',
 'title.order'=>'Your service order confirmation','title.request-information'=>'More information needed','title.record-submission'=>'Submission recorded','title.record-response'=>'Official response available','title.withdraw'=>'Withdrawal received','title.payment-received'=>'Payment received','title.complaint'=>'Message received','title.privacy-request'=>'Data protection request received','title.update'=>'Case update',
 'step.payment-received'=>['We have received your payment. Thank you! You can see the current status of your order at any time in your case.'],
 'step.request-information'=>['We need something more from you. You will find the question in your case. Work pauses until you reply.'],
 'step.record-submission'=>['We have submitted your request to Beitragsservice. The receipt is in your case. Beitragsservice makes the decision; its reply can take a few weeks.','Were you happy with our service? We would appreciate your review. It takes a minute: open your case using the link below.'],
 'step.complaint'=>['Thank you for your message to your case worker. We will take it into account for your order and contact you by email if needed.'],
 'step.privacy-request'=>['We have received your data protection request and will reply by email.'],
 'step.record-response'=>['The reply from Beitragsservice is in your case. Please check it.'],
 'case.linkIntro'=>'Your case (personal link, valid for 14 days, please do not share):','case.linkNote'=>'Personal link, valid for 14 days. Please do not share it.',
 'access.title'=>'Private access','access.body'=>'Open your case. This one-time link replaces your previous browser access.','access.note'=>'The link is valid for 30 minutes.',
 'verify.subject'=>'Your verification code: {code}','verify.title'=>'Your verification code','verify.preheader'=>'Verification code {code}','verify.use'=>'Enter this code in the order form. It is valid for 30 minutes.','verify.ignore'=>'If you did not start an order, you can ignore this email.',
 'pay.title'=>'Your order is in progress','pay.preheader'=>'Payment details and order confirmation','pay.intro'=>'We have started working on your order and are waiting for your payment of {amount}.',
 'pay.transfer'=>'Please transfer','pay.recipient'=>'Recipient','pay.amount'=>'Amount','pay.reference'=>'Payment reference','pay.referenceNote'=>'Please always enter the payment reference so that we can match your payment.',
 'pay.girocode'=>'GiroCode: scan with your banking app to fill in the transfer.','pay.girocodeText'=>'GiroCode to scan with your banking app: see the attachment and your case page.',
 'pay.yourOrder'=>'Your order','pay.orderNumber'=>'Order number','pay.service'=>'Service','pay.orderedOn'=>'Ordered on','pay.pdf'=>'Your order confirmation with all details and the power of attorney is attached (PDF).',
 'mail.order'=>'Order','mail.open'=>'Open your case','mail.private'=>'We are a private service provider and not affiliated with ARD, ZDF, Deutschlandradio or Beitragsservice.','mail.auto'=>'This email was sent automatically.',
 'pdf.file'=>'confirmation','pdf.confirmation'=>'Order confirmation','pdf.authority'=>'Power of attorney','pdf.date'=>'Date','pdf.price'=>'Price','pdf.page'=>'Page','pdf.provider'=>'Provider','pdf.customer'=>'Customer',
 'pdf.payment'=>'Payment by bank transfer','pdf.referenceNote'=>'Please always enter the payment reference.','pdf.girocode'=>'GiroCode: scan with your banking app',
 'pdf.data'=>'Your information','pdf.declarations'=>'Declarations','pdf.accepted'=>'Accepted','pdf.notRequested'=>'Not requested','pdf.terms'=>'Service terms','pdf.withdrawal'=>'Withdrawal','pdf.privacy'=>'Privacy',
 'pdf.place'=>'Place, date','pdf.signature'=>'Signature','pdf.note'=>'We are a private service provider, not affiliated with ARD, ZDF, Deutschlandradio or Beitragsservice. Receiving an order, submitting an application and the official decision are separate steps.',
],
];

function rb_lang(mixed $lang):string{return is_string($lang)&&isset(RB_TEXT[$lang])?$lang:'de';}
// The language the visitor currently sees: sent by the site script with every API call, the language cookie otherwise.
function request_lang():string{return rb_lang($_SERVER['HTTP_X_SITE_LANGUAGE']??$_COOKIE['site-language']??'de');}
function mt(string $key,string $lang,array $vars=[]):string|array{$v=RB_TEXT[$lang][$key]??RB_TEXT['en'][$key]??RB_TEXT['de'][$key]??$key;return is_string($v)&&$vars?strtr($v,array_combine(array_map(fn($k)=>'{'.$k.'}',array_keys($vars)),array_values($vars))):$v;}
// Picks one language from a ['de'=>…,'en'=>…] label (rules.json titles and field labels).
function pick_lang(array $labels,string $lang):string{return (string)($labels[$lang]??$labels['en']??$labels['de']??reset($labels));}
function mail_money(int $cents,string $lang):string{return mt('money',$lang,['a'=>number_format($cents/100,2,mt('dec',$lang),mt('thou',$lang))]);}
