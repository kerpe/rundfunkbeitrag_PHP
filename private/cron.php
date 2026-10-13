<?php
// Hostinger cron command: php /ABSOLUTE/PATH/private/cron.php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
umask(0077);
require __DIR__.'/app/bootstrap.php';
try{$result=dispatch_mail();sql('DELETE FROM rb_records WHERE expires_at IS NOT NULL AND expires_at<?',[time()]);put('settings:jobs','settings',['updated_at'=>now(),'delivery'=>$result]);echo "Mail queue processed.\n";}catch(Throwable){fwrite(STDERR,"Mail job failed; check administrator delivery tools.\n");exit(1);}
