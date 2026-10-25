<?php
declare(strict_types=1);
// GiroCode (EPC069-12 SEPA credit transfer QR). The QR code is generated here, without any outside service:
// byte mode, error correction level M (required by EPC), automatic version and mask (ISO/IEC 18004).

function girocode_payload(array $o):?string{
  $iban=strtoupper(preg_replace('/\s/','',(string)($o['iban']??'')));$bic=strtoupper(preg_replace('/\s/','',(string)($o['bic']??'')));
  $name=girocode_text((string)($o['beneficiary']??''));$ref=girocode_text((string)($o['reference']??''));
  if(!valid_iban($iban)||$name===''||$ref==='')return null;
  if(!preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',$bic))$bic='';
  $cents=(int)($o['feeCents']??4900);if($cents<1||$cents>99999999999)return null;
  return implode("\n",['BCD','002','1','SCT',$bic,mb_substr($name,0,70),$iban,'EUR'.number_format($cents/100,2,'.',''),'','',mb_substr($ref,0,140)]);
}

// Banking apps expect the SEPA Latin set (German umlauts are widely accepted), so e.g. "Gėrimų" becomes "Gerimu".
function girocode_text(string $v):string{$v=strtr($v,['ą'=>'a','č'=>'c','ę'=>'e','ė'=>'e','į'=>'i','š'=>'s','ų'=>'u','ū'=>'u','ž'=>'z','Ą'=>'A','Č'=>'C','Ę'=>'E','Ė'=>'E','Į'=>'I','Š'=>'S','Ų'=>'U','Ū'=>'U','Ž'=>'Z','ł'=>'l','Ł'=>'L','ń'=>'n','ś'=>'s','ź'=>'z','ż'=>'z','ć'=>'c','ā'=>'a','ē'=>'e','ī'=>'i','ķ'=>'k','ļ'=>'l','ņ'=>'n','ģ'=>'g','é'=>'e','è'=>'e','á'=>'a','à'=>'a','ó'=>'o','ò'=>'o','í'=>'i','ú'=>'u','ç'=>'c','ñ'=>'n']);
  return trim(preg_replace('/\s+/',' ',preg_replace('/[^A-Za-z0-9ÄÖÜäöüß \/?:().,\'+&-]/u','',$v)));}

function girocode_svg(array $o):?string{$p=girocode_payload($o);if($p===null)return null;$m=qr_matrix($p);$q=4;$u=4;$w=(count($m)+2*$q)*$u;$path='';
  // Plain pixel coordinates without viewBox scaling: dompdf draws a scaled viewBox wrongly.
  foreach($m as $y=>$row)foreach($row as $x=>$dark)if($dark)$path.='M'.(($x+$q)*$u).' '.(($y+$q)*$u)."h{$u}v{$u}h-{$u}z";
  return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$w.' '.$w.'" width="'.$w.'" height="'.$w.'" shape-rendering="crispEdges"><rect width="'.$w.'" height="'.$w.'" fill="#fff"/><path fill="#000" d="'.$path.'"/></svg>';}

function girocode_data_uri(array $o):?string{$svg=girocode_svg($o);return $svg===null?null:'data:image/svg+xml;base64,'.base64_encode($svg);}

function qr_gf_mul(int $x,int $y):int{$z=0;for($i=7;$i>=0;$i--){$z=($z<<1)^(($z>>7)*0x11D);$z^=(($y>>$i)&1)*$x;}return $z&0xFF;}

function qr_rs_remainder(array $data,int $degree):array{
  $div=array_fill(0,$degree-1,0);$div[]=1;$root=1;
  for($i=0;$i<$degree;$i++){for($j=0;$j<$degree;$j++){$div[$j]=qr_gf_mul($div[$j],$root);if($j+1<$degree)$div[$j]^=$div[$j+1];}$root=qr_gf_mul($root,2);}
  $r=array_fill(0,$degree,0);
  foreach($data as $b){$f=$b^array_shift($r);$r[]=0;for($i=0;$i<$degree;$i++)$r[$i]^=qr_gf_mul($div[$i],$f);}
  return $r;}

function qr_raw_modules(int $v):int{$r=(16*$v+128)*$v+64;if($v>=2){$a=intdiv($v,7)+2;$r-=(25*$a-10)*$a-55;if($v>=7)$r-=36;}return $r;}

function qr_align_positions(int $v):array{if($v===1)return [];$a=intdiv($v,7)+2;$step=intdiv($v*8+$a*3+5,$a*4-4)*2;$r=[];
  for($i=0,$pos=$v*4+10;$i<$a-1;$i++,$pos-=$step)array_unshift($r,$pos);array_unshift($r,6);return $r;}

function qr_matrix(string $text):array{
  // Level M tables, index = version
  $eccPerBlock=[0,10,16,26,18,24,16,18,22,22,26,30,22,22,24,24,28,28,26,26,26,26,28,28,28,28,28,28,28,28,28,28,28,28,28,28,28,28,28,28,28];
  $blocks=[0,1,1,1,2,2,4,4,4,5,5,5,8,9,9,10,10,11,13,14,16,17,17,18,20,21,23,25,26,28,29,31,33,35,37,38,40,43,45,47,49];
  $bytes=array_values(unpack('C*',$text)?:[]);$len=count($bytes);
  for($v=1;$v<=40;$v++){$cap=intdiv(qr_raw_modules($v),8)-$eccPerBlock[$v]*$blocks[$v];if(4+($v<10?8:16)+8*$len<=$cap*8)break;}
  if($v>40)throw new RuntimeException('QR_TOO_LONG');
  // Data codewords
  $bits=[];$add=function(int $val,int $n)use(&$bits){for($i=$n-1;$i>=0;$i--)$bits[]=($val>>$i)&1;};
  $add(4,4);$add($len,$v<10?8:16);foreach($bytes as $b)$add($b,8);
  $add(0,min(4,$cap*8-count($bits)));$add(0,(8-count($bits)%8)%8);
  $data=[];foreach(array_chunk($bits,8) as $c)$data[]=bindec(implode('',$c));
  for($pad=0xEC;count($data)<$cap;$pad^=0xEC^0x11)$data[]=$pad;
  // Error correction and interleaving
  $nb=$blocks[$v];$ecl=$eccPerBlock[$v];$raw=intdiv(qr_raw_modules($v),8);$short=$nb-$raw%$nb;$shortLen=intdiv($raw,$nb);$parts=[];
  for($i=0,$k=0;$i<$nb;$i++){$dl=$shortLen-$ecl+($i<$short?0:1);$d=array_slice($data,$k,$dl);$k+=$dl;$blk=array_merge($d,qr_rs_remainder($d,$ecl));if($i<$short)array_splice($blk,$dl,0,[0]);$parts[]=$blk;}
  $words=[];for($i=0;$i<count($parts[0]);$i++)for($j=0;$j<$nb;$j++)if($i!==$shortLen-$ecl||$j>=$short)$words[]=$parts[$j][$i];
  // Function patterns
  $size=$v*4+17;$m=array_fill(0,$size,array_fill(0,$size,false));$fn=$m;
  $set=function(int $x,int $y,bool $d)use(&$m,&$fn){$m[$y][$x]=$d;$fn[$y][$x]=true;};
  for($i=0;$i<$size;$i++){$set(6,$i,$i%2===0);$set($i,6,$i%2===0);}
  foreach([[3,3],[$size-4,3],[3,$size-4]] as [$cx,$cy])for($dy=-4;$dy<=4;$dy++)for($dx=-4;$dx<=4;$dx++){$x=$cx+$dx;$y=$cy+$dy;if($x<0||$y<0||$x>=$size||$y>=$size)continue;$dist=max(abs($dx),abs($dy));$set($x,$y,$dist!==2&&$dist!==4);}
  $al=qr_align_positions($v);$na=count($al);
  for($i=0;$i<$na;$i++)for($j=0;$j<$na;$j++){if(($i===0&&$j===0)||($i===0&&$j===$na-1)||($i===$na-1&&$j===0))continue;for($dy=-2;$dy<=2;$dy++)for($dx=-2;$dx<=2;$dx++)$set($al[$i]+$dx,$al[$j]+$dy,max(abs($dx),abs($dy))!==1);}
  $format=function(int $mask)use(&$set,$size){$d=$mask;$r=$d;for($i=0;$i<10;$i++)$r=($r<<1)^(($r>>9)*0x537);$b=(($d<<10)|$r)^0x5412;$bit=fn($i)=>(($b>>$i)&1)===1;
    for($i=0;$i<=5;$i++)$set(8,$i,$bit($i));$set(8,7,$bit(6));$set(8,8,$bit(7));$set(7,8,$bit(8));for($i=9;$i<15;$i++)$set(14-$i,8,$bit($i));
    for($i=0;$i<8;$i++)$set($size-1-$i,8,$bit($i));for($i=8;$i<15;$i++)$set(8,$size-15+$i,$bit($i));$set(8,$size-8,true);};
  $format(0);
  if($v>=7){$r=$v;for($i=0;$i<12;$i++)$r=($r<<1)^(($r>>11)*0x1F25);$b=($v<<12)|$r;for($i=0;$i<18;$i++){$d=(($b>>$i)&1)===1;$a=$size-11+$i%3;$c=intdiv($i,3);$set($a,$c,$d);$set($c,$a,$d);}}
  // Codewords in zigzag order
  $i=0;$total=count($words)*8;
  for($right=$size-1;$right>=1;$right-=2){if($right===6)$right=5;for($vert=0;$vert<$size;$vert++)for($j=0;$j<2;$j++){$x=$right-$j;$y=(($right+1)&2)===0?$size-1-$vert:$vert;if(!$fn[$y][$x]&&$i<$total){$m[$y][$x]=(($words[$i>>3]>>(7-($i&7)))&1)===1;$i++;}}}
  // Pick the mask with the lowest penalty
  $masked=function(int $mask)use(&$m,&$fn,$size){$out=$m;for($y=0;$y<$size;$y++)for($x=0;$x<$size;$x++){if($fn[$y][$x])continue;$inv=match($mask){0=>($x+$y)%2===0,1=>$y%2===0,2=>$x%3===0,3=>($x+$y)%3===0,4=>(intdiv($x,3)+intdiv($y,2))%2===0,5=>$x*$y%2+$x*$y%3===0,6=>($x*$y%2+$x*$y%3)%2===0,default=>(($x+$y)%2+$x*$y%3)%2===0};if($inv)$out[$y][$x]=!$out[$y][$x];}return $out;};
  $best=null;$bestScore=PHP_INT_MAX;$base=$m;
  for($mask=0;$mask<8;$mask++){$m=$base;$format($mask);$cand=$masked($mask);$score=qr_penalty($cand);if($score<$bestScore){$bestScore=$score;$best=$cand;}}
  return $best;}

function qr_penalty(array $m):int{$n=count($m);$p=0;$dark=0;
  $lines=[];for($i=0;$i<$n;$i++){$lines[]=$m[$i];$lines[]=array_column($m,$i);}
  foreach($lines as $line){$run=1;for($i=1;$i<=$n;$i++){if($i<$n&&$line[$i]===$line[$i-1]){$run++;continue;}if($run>=5)$p+=$run-2;$run=1;}
    $s=implode('',array_map(fn($b)=>$b?'1':'0',$line));$p+=40*(substr_count($s,'10111010000')+substr_count($s,'00001011101'));}
  for($y=0;$y<$n;$y++)for($x=0;$x<$n;$x++){if($m[$y][$x])$dark++;if($x<$n-1&&$y<$n-1&&$m[$y][$x]===$m[$y][$x+1]&&$m[$y][$x]===$m[$y+1][$x]&&$m[$y][$x]===$m[$y+1][$x+1])$p+=3;}
  $p+=10*intdiv(abs($dark*20-$n*$n*10),$n*$n);return $p;}
