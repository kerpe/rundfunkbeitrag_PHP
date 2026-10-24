<?php
// Hostinger cron command: php /ABSOLUTE/PATH/private/cron.php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
umask(0077);
require __DIR__.'/app/bootstrap.php';
function purge_stale_drafts():int{$n=0;foreach(sql("SELECT id FROM rb_records WHERE kind='case' AND owner_hash<>'erasing' AND updated_at<? AND JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.state'))='draft' LIMIT 200",[time()-30*86400])->fetchAll() as $row){db()->beginTransaction();$r=record($row['id'],true);if(!$r||$r['value']['state']!=='draft'||(int)$r['updated_at']>=time()-30*86400){db()->commit();continue;}$c=$r['value'];sql('DELETE FROM rb_records WHERE parent_id=? OR id=?',[$c['id'],'case:'.$c['id']]);db()->commit();foreach($c['documents'] as $d){$f=private_root().'/data/uploads/'.$d['objectKey'];if(is_file($f))unlink($f);}$n++;}return $n;}
try{$result=dispatch_mail();$purged=purge_stale_drafts();sql('DELETE FROM rb_records WHERE expires_at IS NOT NULL AND expires_at<?',[time()]);put('settings:jobs','settings',['updated_at'=>now(),'delivery'=>$result,'draftsPurged'=>$purged]);echo "Mail queue processed.\n";}catch(Throwable){fwrite(STDERR,"Mail job failed; check administrator delivery tools.\n");exit(1);}
