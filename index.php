<?php
set_time_limit(0);

require_once "config/environment.php";
require_once "lib/core.php";

$version = "0.01";
$appName = "Knigosite downloader";

$messages = array(
    "help" => "$appName Copyright (C) 2012 Дамир Махмутов\n"
);

global $config;
$core = new Core($config->getConfig());
$options = $core->getOptions($argv);

Console::WriteLine("Start");

/*
 * TODO: Добавить опции:
 * - Выбор формата
 */

if ( ! count($options) ||  $core->isOptionExists('help') ) {
    Console::WriteLine($messages['help']);
    exit(0);
} elseif ( $core->isOptionExists('a') || $core->isOptionExists('author') ) {
    /*
     * Поиск по автору
     */
    $author = $core->getOptionValue('a');
    $core->downloadAuthorBooks($author);
} elseif ( in_array('t', $options) || in_array('title', $options) ) {
    /*
     * Поиск по названию
     */
    $title = $core->getOptionValue('t');
    $core->downloadFromTitle($title); 
}