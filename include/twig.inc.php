<?php
require_once __DIR__ . '/../vendors/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(realpath(__DIR__ . '/../templates'));
$twig = new Twig_Environment($loader, array(
    'cache' => realpath(__DIR__ . '/../cache'),
    'charset' => 'utf-8',
    'auto_reload' => true
));
