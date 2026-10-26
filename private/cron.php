<?php
// Hostinger cron command: php /ABSOLUTE/PATH/private/cron.php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
umask(0077);
require __DIR__.'/app/bootstrap.php';
function purge_stale_drafts():int{$n=0;foreach(sql("SELECT id FROM rb_records WHERE kind='case' AND owner_hash<>'erasing' AND updated_at<? AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.state'))='draft' LIMIT 200",[time()-30*86400])->fetchAll() as $row){db()->beginTransaction();$r=record($row['id'],true);if(!$r||$r['value']['state']!=='draft'||(int)$r['updated_at']>=time()-30*86400){db()->commit();continue;}$c=$r['value'];sql('DELETE FROM rb_records WHERE parent_id=? OR id=?',[$c['id'],'case:'.$c['id']]);db()->commit();foreach($c['documents'] as $d){$f=private_root().'/data/uploads/'.$d['objectKey'];if(is_file($f))unlink($f);}$n++;}return $n;}
// Retention (privacy notice §11): customer-uploaded evidence goes DOC_KEEP_DAYS after submission (or closing); the whole case CASE_KEEP_DAYS later. Staff receipts stay until then; invoices are kept for accounting.
const DOC_KEEP_DAYS=180;const CASE_KEEP_DAYS=1095;
function retention_base(array $c):int{return strtotime($c['submission']['at']??$c['updatedAt'])?:time();}
function purge_retention():array{$docs=0;$cases=0;foreach(sql("SELECT id FROM rb_records WHERE kind='case' AND owner_hash<>'erasing' AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.state')) IN ('submitted','closed') LIMIT 500")->fetchAll() as $row){db()->beginTransaction();$r=record($row['id'],true);$c=$r['value']??null;if(!$c||!in_array($c['state'],['submitted','closed'],true)){db()->commit();continue;}$base=retention_base($c);$files=[];
  if($base<time()-CASE_KEEP_DAYS*86400){foreach($c['documents'] as $d)$files[]=$d['objectKey']??'';sql("DELETE FROM rb_records WHERE (parent_id=? AND kind<>'invoice') OR id=?",[$c['id'],'case:'.$c['id']]);put('erasure:'.$c['id'],'erasure',['id'=>$c['id'],'at'=>now(),'by'=>'retention','status'=>'complete']);$cases++;}
  elseif($base<time()-DOC_KEEP_DAYS*86400&&empty($c['documentsPurgedAt'])){$keep=[];foreach($c['documents'] as $d){if(in_array($d['kind'],['submissionReceipt','officialResponse'],true)){$keep[]=$d;continue;}$files[]=$d['objectKey']??'';sql('DELETE FROM rb_records WHERE id=?',['document:'.$d['id']]);$docs++;}$c['documents']=$keep;$c['documentsPurgedAt']=now();$c['version']++;$c['updatedAt']=now();put('case:'.$c['id'],'case',$c,'',$r['owner_hash']);}
  db()->commit();foreach($files as $f)if($f!==''&&is_file($file=private_root().'/data/uploads/'.$f))unlink($file);}
 return ['documents'=>$docs,'cases'=>$cases];}
try{$result=dispatch_mail();$purged=purge_stale_drafts();$retention=purge_retention();sql('DELETE FROM rb_records WHERE expires_at IS NOT NULL AND expires_at<?',[time()]);put('settings:jobs','settings',['updated_at'=>now(),'delivery'=>$result,'draftsPurged'=>$purged,'retention'=>$retention]);echo "Mail queue processed.\n";}catch(Throwable){fwrite(STDERR,"Mail job failed; check administrator delivery tools.\n");exit(1);}
