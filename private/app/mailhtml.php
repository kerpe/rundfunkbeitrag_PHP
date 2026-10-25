<?php
declare(strict_types=1);
// HTML version of the customer emails, in the site's colours. Email clients only keep inline styles and tables,
// and most of them cannot load web fonts, so the font list falls back to the system fonts.
// The plain-text part stays the same and is always sent along.
const MAIL_FONT="'Instrument Sans','Segoe UI',Helvetica,Arial,sans-serif";
const MAIL_C=['dark'=>'#123A34','petrol'=>'#0F6E62','cream'=>'#F7F4EE','apricot'=>'#F2A65A','text'=>'#1E2B28','muted'=>'#5E6E6A','line'=>'#E4DED3','soft'=>'#FBF3E8'];

// "Deutsch\nEnglish" blocks separated by empty lines become [[de,en],...].
function mail_pairs(string $text):array{$out=[];foreach(preg_split('/\n{2,}/',trim($text)) as $block)if(trim($block)!==''){$l=explode("\n",$block,2);$out[]=[trim($l[0]),trim($l[1]??'')];}return $out;}

function mail_p(array $pair,int $size=15):string{$c=MAIL_C;return '<p style="margin:0 0 16px;font-family:'.MAIL_FONT.';font-size:'.$size.'px;line-height:1.55;color:'.$c['text'].'">'.h($pair[0]).($pair[1]!==''?'<br><span style="color:'.$c['muted'].';font-size:'.($size-1).'px">'.h($pair[1]).'</span>':'').'</p>';}

function mail_button(string $url,string $label):string{$c=MAIL_C;return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 12px"><tr><td bgcolor="'.$c['petrol'].'" style="border-radius:6px;background:'.$c['petrol'].'"><a href="'.h($url).'" style="display:inline-block;padding:13px 24px;font-family:'.MAIL_FONT.';font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:6px">'.h($label).'</a></td></tr></table>';}

function mail_row(string $label,string $value,bool $strong=false,bool $mark=false):string{$c=MAIL_C;return '<tr><td style="padding:9px 12px 9px 0;border-top:1px solid '.$c['line'].';font-family:'.MAIL_FONT.';font-size:12px;line-height:1.4;color:'.$c['muted'].';vertical-align:top;width:42%">'.$label.'</td><td style="padding:9px 0;border-top:1px solid '.$c['line'].';font-family:'.MAIL_FONT.';font-size:15px;line-height:1.4;color:'.$c['text'].';vertical-align:top;'.($strong?'font-weight:600;':'').'word-break:break-word">'.($mark?'<span style="background:'.$c['soft'].';border-left:3px solid '.$c['apricot'].';padding:2px 8px;font-weight:600">'.h($value).'</span>':h($value)).'</td></tr>';}

// $p: email payload with a 'view' (built in mail.php); $qrCid: content id of the GiroCode image, if attached.
function mail_html(array $p,?string $qrCid=null):string{$v=$p['view'];$c=MAIL_C;$s=settings();$f=MAIL_FONT;
 $title=explode(' / ',$v['title'],2);$body='';
 if(($v['ref']??'')!=='')$body.='<p style="margin:0 0 6px;font-family:'.$f.';font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:'.$c['petrol'].';font-weight:600">Auftrag / Order '.h($v['ref']).'</p>';
 $body.='<h1 style="margin:0 0 4px;font-family:'.$f.';font-size:24px;line-height:1.25;font-weight:600;color:'.$c['dark'].'">'.h($title[0]).'</h1>'.(isset($title[1])?'<p style="margin:0 0 22px;font-family:'.$f.';font-size:15px;color:'.$c['muted'].'">'.h($title[1]).'</p>':'<div style="height:18px"></div>');
 foreach($v['paras']??[] as $pair)$body.=mail_p($pair);
 if(isset($v['code']))$body.='<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:4px 0 20px"><tr><td bgcolor="'.$c['cream'].'" style="background:'.$c['cream'].';border:1px solid '.$c['line'].';border-radius:8px;padding:14px 26px;font-family:Consolas,Menlo,monospace;font-size:30px;letter-spacing:.3em;font-weight:700;color:'.$c['dark'].'">'.h($v['code']).'</td></tr></table>';
 if(isset($v['pay'])){$pay=$v['pay'];
  $body.='<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="'.$c['cream'].'" style="background:'.$c['cream'].';border:1px solid '.$c['line'].';border-radius:8px;margin:6px 0 20px"><tr><td style="padding:20px 22px 12px">'
   .'<p style="margin:0 0 12px;font-family:'.$f.';font-size:16px;font-weight:600;color:'.$c['dark'].'">Bitte überweisen Sie<span style="font-weight:400;color:'.$c['muted'].'"> / Please transfer</span></p>'
   .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">'
   .mail_row('Empfänger<br>Recipient',$pay['beneficiary']).mail_row('IBAN',trim(chunk_split(strtoupper(preg_replace('/\s/','',$pay['iban'])),4,' ')),true).mail_row('BIC',$pay['bic'])
   .mail_row('Betrag<br>Amount',$pay['amount'].' €',true).mail_row('Verwendungszweck<br>Payment reference',$v['ref'],true,true).'</table>'
   .($qrCid?'<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:14px 0 6px"><tr><td style="vertical-align:middle;padding-right:16px"><img src="cid:'.h($qrCid).'" width="120" height="120" alt="GiroCode" style="display:block;border:1px solid '.$c['line'].';border-radius:4px;background:#ffffff"></td><td style="vertical-align:middle;font-family:'.$f.';font-size:13px;line-height:1.5;color:'.$c['text'].'">GiroCode: mit Ihrer Banking-App scannen, alle Angaben werden übernommen.<br><span style="color:'.$c['muted'].'">Scan with your banking app to fill in the transfer.</span></td></tr></table>':'')
   .'</td></tr></table>';
  $body.=mail_p(['Bitte geben Sie bei der Überweisung unbedingt den Verwendungszweck an, damit wir Ihre Zahlung zuordnen können.','Please always enter the payment reference so that we can match your payment.'],14);
  $body.='<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 22px"><tr><td style="font-family:'.$f.';font-size:16px;font-weight:600;color:'.$c['dark'].';padding:0 0 4px">Ihr Auftrag<span style="font-weight:400;color:'.$c['muted'].'"> / Your order</span></td></tr><tr><td><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">'
   .mail_row('Auftragsnummer<br>Order number',$v['ref']).mail_row('Leistung<br>Service',$pay['service']).mail_row('Bestellt am<br>Ordered on',$pay['at'])
   .'</table></td></tr></table>'.mail_p(['Ihre Auftragsbestätigung mit allen Angaben und der Vollmacht finden Sie im Anhang (PDF).','Your order confirmation with all details and the power of attorney is attached (PDF).'],14);}
 if(isset($v['link'])){$body.=mail_button($v['link'],$v['button']??'Vorgang öffnen / Open your case');
  if(isset($v['linkNote']))$body.='<p style="margin:0 0 6px;font-family:'.$f.';font-size:13px;line-height:1.5;color:'.$c['muted'].'">'.h($v['linkNote'][0]).'<br>'.h($v['linkNote'][1]).'</p>';
  $body.='<p style="margin:0;font-family:'.$f.';font-size:12px;line-height:1.5;color:'.$c['muted'].';word-break:break-all">Link: <a href="'.h($v['link']).'" style="color:'.$c['petrol'].'">'.h($v['link']).'</a></p>';}
 foreach($v['after']??[] as $pair)$body.='<div style="height:16px"></div>'.mail_p($pair,14);
 $addr=trim(preg_replace('/\s*\R\s*/',', ',trim((string)($s['providerAddress']??''))),', ');$mail=trim((string)($s['supportEmail']??''));$name=trim((string)($s['providerName']??''));
 $foot=($name!==''?'<strong style="color:'.$c['text'].';font-weight:600">'.h($name).'</strong>'.($addr!==''?' · '.h($addr):'').'<br>':'').($mail!==''?'<a href="mailto:'.h($mail).'" style="color:'.$c['petrol'].'">'.h($mail).'</a><br>':'')
  .'<span style="display:block;margin-top:10px">Wir sind ein privater Dienstleister und nicht mit ARD, ZDF, Deutschlandradio oder dem Beitragsservice verbunden.<br>We are a private service provider and not affiliated with ARD, ZDF, Deutschlandradio or Beitragsservice.</span>';
 $pre=h($v['preheader']??($title[0].(($v['ref']??'')!==''?' · '.$v['ref']:'')));
 return '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light only"><meta name="supported-color-schemes" content="light"><title>'.h($p['subject']).'</title></head>'
  .'<body style="margin:0;padding:0;background:'.$c['cream'].'"><div style="display:none;max-height:0;overflow:hidden;opacity:0">'.$pre.'</div>'
  .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="'.$c['cream'].'" style="background:'.$c['cream'].'"><tr><td align="center" style="padding:24px 12px">'
  .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%">'
  .'<tr><td bgcolor="'.$c['dark'].'" style="background:'.$c['dark'].';border-radius:10px 10px 0 0;padding:20px 28px"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="width:10px;height:22px;background:'.$c['apricot'].';border-radius:2px"></td><td style="padding-left:12px;font-family:'.$f.';font-size:18px;font-weight:600;letter-spacing:.01em;color:#ffffff">Rundfunkbeitrag Support</td></tr></table></td></tr>'
  .'<tr><td bgcolor="#ffffff" style="background:#ffffff;padding:30px 28px 28px;border-left:1px solid '.$c['line'].';border-right:1px solid '.$c['line'].'">'.$body.'</td></tr>'
  .'<tr><td bgcolor="#ffffff" style="background:#ffffff;border:1px solid '.$c['line'].';border-top:3px solid '.$c['petrol'].';border-radius:0 0 10px 10px;padding:18px 28px;font-family:'.$f.';font-size:12px;line-height:1.55;color:'.$c['muted'].'">'.$foot.'</td></tr>'
  .'<tr><td style="padding:14px 28px 0;font-family:'.$f.';font-size:11px;line-height:1.5;color:'.$c['muted'].';text-align:center">Diese E-Mail wurde automatisch versendet. · This email was sent automatically.</td></tr>'
  .'</table></td></tr></table></body></html>';}
