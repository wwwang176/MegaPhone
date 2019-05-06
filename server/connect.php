<?php

include __DIR__.'/config.php';

$DBC=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWD);
$DBC->exec("set names utf8");

?>