<?php
require 'Phake.php';
Phake::setClient(Phake::CLIENT_PHPUNIT);

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
