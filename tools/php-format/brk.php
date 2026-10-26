<?php
// insert a newline after a statement-closing "}" that is directly followed by another statement
foreach (array_slice($argv,1) as $f) {
  $t = token_get_all(file_get_contents($f)); $out=''; $stack=[]; $n=count($t);
  for ($i=0;$i<$n;$i++) {
    $tok=$t[$i]; $s=is_array($tok)?$tok[1]:$tok;
    if (is_array($tok) && in_array($tok[0],[T_CURLY_OPEN,T_DOLLAR_OPEN_CURLY_BRACES],true)) $stack[]='interp';
    elseif ($s==='{') $stack[]='code';
    elseif ($s==='}') {
      $kind=array_pop($stack); $out.=$s;
      if ($kind==='code' && $i+1<$n) { $nx=$t[$i+1]; $ns=is_array($nx)?$nx[1]:$nx;
        $skip=[')',',',';',']','.','->','?->','::','(','['];
        $isWs=is_array($nx)&&$nx[0]===T_WHITESPACE;
        $isCont=is_array($nx)&&in_array($nx[0],[T_ELSE,T_ELSEIF,T_CATCH,T_FINALLY,T_WHILE],true);
        if(!$isWs && !$isCont && !in_array($ns,$skip,true) && !(is_array($nx)&&$nx[0]===T_CLOSE_TAG) && $ns!=='}') $out.="\n";
      }
      continue;
    }
    $out.=$s;
  }
  file_put_contents($f,$out);
}
