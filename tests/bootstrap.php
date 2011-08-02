<?php

require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY']);
$loader->registerPrefixes(array(
    'Twig_'            => $_SERVER['TWIG'],
));

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'TimeOut')) {
        $path = str_replace( '\\', '/', $class ) . '.php';
        require_once __DIR__.'/../src/'.$path;
        return true;
    }
});
