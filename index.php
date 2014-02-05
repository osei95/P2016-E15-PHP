<?php

$f3 = require('inc/functions.php');
$f3 = require('lib/base.php');
$f3->config('config/config.ini');
$f3->config('config/routes.ini');
$f3->config('config/daabase.ini');
$f3->config('config/apis.ini');

$f3->run();

?>