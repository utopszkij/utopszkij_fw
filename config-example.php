<?php 
declare(strict_types=1);
DEFINE('SITETITLE','Utopszkij_fw');
DEFINE('SITEURL','https://szakacskonyv.great-site.net');
DEFINE('REWRITE',true);
DEFINE('LNG','hu');

DEFINE('HOST','localhost');
DEFINE('USER','mysql-user');
DEFINE('PSW','mysql-user-password');
DEFINE('DBNAME','szakacskonyv');

DEFINE('MAIL_HOST','');
DEFINE('MAIL_PORT','465');
DEFINE('MAIL_USERNAME','');
DEFINE('MAIL_PASSWORD','');
DEFINE('MAIL_ENCRYPTION','SSL');
DEFINE('MAIL_FROM_ADDRESS','');
DEFINE('MAIL_FROM_NAME','');
DEFINE('MAIL_WAIT_SEC','130');


DEFINE('MULTIUSER',true); // true: több felhasználós mód, false: egy felhasználós mód
DEFINE('ADMIN','admin'); // system admin nick név

DEFINE('FB_APPID','123456');
DEFINE('FB_SECRET','123456');
DEFINE('FB_REDIRECT',SITEURL.'/vendor/fblogin.php');

DEFINE('GOOGLE_APPID','123456');
DEFINE('GOOGLE_SECRET','123456');
DEFINE('GOOGLE_REDIRECT',SITEURL.'/vendor/googlelogin.php');

?>
