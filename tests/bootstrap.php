<?php

$loader = include __DIR__ . '/../vendor/autoload.php';
if (!$loader) {
    die(<<<EOT
You need to install the project dependencies using Composer:
$ wget http://getcomposer.org/composer.phar
OR
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install --dev
$ phpunit
EOT
    );
}

date_default_timezone_set('UTC');
error_reporting(E_ALL | E_STRICT);

use Doctrine\ORM\Tools\Setup;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Indaxia\OTR\Tests\Mocks;
use Indaxia\OTR\Annotations\PolicyResolver;
use Indaxia\OTR\Annotations\PolicyResolverProfiler;

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/Entity"), $isDevMode);

$entityManager = Mocks\EntityManagerMock::create(
    new Mocks\ConnectionMock([], new Mocks\DriverMock()), 
    $config
);

global $useProfiler, $argv;
$useProfiler = getenv('PROFILER');
$profilerDetails = getenv('PROFILER_DETAILS');

/** Creates a new PolicyResolver or PolicyResolverProfiler depending on arg 'profiler' */
function newPR($options = 0) {
    global $useProfiler;
    return $useProfiler
           ? new PolicyResolverProfiler($profilerDetails ? ($options | PolicyResolverProfiler::PRIORITY_DETAILS) : $options)
           : new PolicyResolver($options);
}

/** Prints PolicyResolverProfiler results depending on arg 'profiler' */
function printPR($pr) {
    global $useProfiler;
    if($useProfiler) {
        echo PHP_EOL;
        echo debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'].' profiling:'.PHP_EOL;
        foreach($pr->results as $r) { echo '    '.$r.PHP_EOL; }
        echo PHP_EOL;         
    }
}