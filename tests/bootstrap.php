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

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/Entity"), $isDevMode);

$entityManager = Mocks\EntityManagerMock::create(
    new Mocks\ConnectionMock([], new Mocks\DriverMock()), 
    $config
);

$useProfiler = false;
if(isset($argv)) { foreach($argv as $i => $a) {
    if($a == 'profiler') {
        $useProfiler = true;
        break;
    }    
} }

function newPR($options) {
    return $useProfiler
           ? new PolicyResolverProfiler($options | PolicyResolverProfiler::PRIORITY_DETAILS)
           : new PolicyResolver($options);
}
function printPR($pr) {
    if($useProfiler) {
        echo PHP_EOL;
        echo 'PolicyResolverProfiler Results:'.PHP_EOL;
        foreach($pr->results as $r) { echo '    '.$r.PHP_EOL; }
        echo PHP_EOL;         
    }
}