<?php

if (class_exists('Icecave\Isolator\Isolator', false)) {
    function icecaveIsolatorPostGeneration($value = null)
    {
        return $value;
    }
}
