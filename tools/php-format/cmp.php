<?php
require __DIR__.'/vendor/autoload.php';
use PhpParser\ParserFactory; use PhpParser\NodeDumper;
$p=(new ParserFactory)->createForNewestSupportedVersion(); $d=new NodeDumper(['dumpComments'=>false,'dumpPositions'=>false]);
$bad=0;
for($i=1;$i<count($argv);$i+=2){
  $a=$d->dump($p->parse(file_get_contents($argv[$i]))); $b=$d->dump($p->parse(file_get_contents($argv[$i+1])));
  // InlineHTML / heredoc whitespace is significant and stays identical in the dump
  if($a!==$b){$bad++;echo "DIFF ".$argv[$i]."\n";} else echo "same ".basename($argv[$i])."\n";
}
exit($bad?1:0);
