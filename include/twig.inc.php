<?php
require_once __DIR__ . '/../vendor/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(realpath(__DIR__ . '/../templates/public'));
$twig = new Twig_Environment($loader, array(
    'cache' => realpath(__DIR__ . '/../cache'),
    'charset' => 'utf-8',
    'auto_reload' => true
));
