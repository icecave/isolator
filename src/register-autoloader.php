<?php
// @codeCoverageIgnoreStart

spl_autoload_register(
    function ($className) {
        if ($className !== 'Icecave\Isolator\Isolator') {
            return;
        }

        $autoloader = new Icecave\Isolator\Detail\Autoloader(
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'isolator',
            new Icecave\Isolator\Detail\CodeGenerator()
        );

        $autoloader->load($className);
    }
);
