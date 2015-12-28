<?php
require_once 'PHPUnit/Framework.php';
require_once 'libfajr/libfajr.php';
Loader::register();
Loader::searchForClasses(__DIR__, true);
ini_set('error_reporting', E_ALL | E_STRICT);
?>
