<?php 
declare(strict_types=1);
DEFINE('SITETITLE','Utopszkij_fw');
DEFINE('SITEURL','https://szakacskonyv.great-site.net');
DEFINE('REWRITE',true);
DEFINE('LNG','hu');
DEFINE('LOGIN_MUST_VERIFYED_EMAIL',true); // csak ellenörzött email -el lehet bejelentkezni
DEFINE('MULTIUSER',true); // true: több felhasználós mód, false: egy felhasználós mód
DEFINE('ADMIN','admin'); // az első system admin user belépési neve
// GDPR
DEFINE('ADATKEZELO','Gipsz Jakab<br />1036 Budapest, Jézusszive utca 61<br />gipsz.jakab@gmail.com<br />https://gipsz-jakab.hu');
DEFINE('ADATFELDOLGOZO','Zabhegyező kft<br />1165 Budapest, Faluvége út 101.<br />info@zabhegyezo.hu<br />Weboldal: www.zabhegyezo.hu');
DEFINE('SIGNO','Fogler Tibor<br>2022.07.28.');

// MYSQL
DEFINE('HOST','localhost');
DEFINE('USER','mysql-user');
DEFINE('PSW','mysql-user-password');
DEFINE('DBNAME','valami');
// SMTP
DEFINE('MAIL_HOST','smtp.valami.hu');
DEFINE('MAIL_PORT','465');
DEFINE('MAIL_USERNAME','valaki@valami.hu');
DEFINE('MAIL_PASSWORD','valami');
DEFINE('MAIL_ENCRYPTION','SSL');
DEFINE('MAIL_FROM_ADDRESS','valaki@valami.hu');
DEFINE('MAIL_FROM_NAME',''valaki);
DEFINE('MAIL_WAIT_SEC','130');
// Facebook login
DEFINE('FB_APPID','123456');
DEFINE('FB_SECRET','123456');
DEFINE('FB_REDIRECT',SITEURL.'/vendor/fblogin.php');
// Google login
DEFINE('GOOGLE_APPID','123456');
DEFINE('GOOGLE_SECRET','123456');
DEFINE('GOOGLE_REDIRECT',SITEURL.'/vendor/googlelogin.php');

?>
