<?php
namespace environment;

$config = parse_ini_file("config.ini");

define("IS_WINDOWS", PHP_OS == "WINNT");