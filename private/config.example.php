<?php
// Installation creates config.php here. Keep the private directory outside public_html.
return [
 'public_url'=>'https://your-domain.example',
 'db_dsn'=>'mysql:host=localhost;dbname=YOUR_DATABASE;charset=utf8mb4',
 'db_user'=>'YOUR_DATABASE_USER', 'db_password'=>'YOUR_DATABASE_PASSWORD',
 'admin_email'=>'you@example.com', 'admin_hash'=>'CREATED_DURING_INSTALLATION',
 'app_secret'=>'CREATED_DURING_INSTALLATION',
 'resend_key'=>'', 'email_from'=>'', 'indexing'=>false,
];
