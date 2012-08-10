<?php
require_once dirname(__FILE__) . "/../lib/config.php";

$config = Config::instance();
$config->setConfig(parse_ini_file("config.ini"));

define("IS_WINDOWS", PHP_OS == "WINNT");