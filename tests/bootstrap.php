<?php

if (!($loader = include __DIR__ . '/../vendor/autoload.php')) {
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
use Doctrine\Tests\Mocks\EntityManagerMock;

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/Entity"), $isDevMode);

$entityManager = EntityManagerMock::create([
    'driverClass'  => 'Doctrine\Tests\Mocks\DriverMock',
    'wrapperClass' => 'Doctrine\Tests\Mocks\ConnectionMock',
    'user'         => 'john',
    'password'     => 'wayne'
], $config);


