<?php

$f3 = require('inc/functions.php');
$f3 = require('lib/base.php');
$f3->config('config/config.ini');
$f3->config('config/routes.ini');
$f3->config('config/database.ini');
$f3->config('config/apis.ini');

$f3->set('dB', new DB\SQL($f3->get('MYSQL_HOST'),$f3->get('MYSQL_USER'),$f3->get('MYSQL_PASSWORD')));

$f3->run();

?>