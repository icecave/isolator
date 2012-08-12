<?php

$autoloader = require __DIR__ . '/../vendor/autoload.php';
$autoloader->add('Icecave\Isolator\TestFixture', __DIR__.'/lib');

Phake::setClient(Phake::CLIENT_PHPUNIT);
