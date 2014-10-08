<?php
namespace Icecave\Isolator;

/**
 * Make calls to global functions via a mockable object.
 */
interface IsolatorInterface
{
    /**
     * Forward a call onto global function.
     *
     * Support is also provided for the following function-esque language constructs:
     *
     *    - exit
     *    - die
     *    - echo
     *    - include
     *    - include_once
     *    - require
     *    - require_once
     *    - new
     *
     * @param string $name      The name of the global function to call.
     * @param array  $arguments The arguments to the function.
     *
     * @return mixed The result of the function call.
     */
    public function __call($name, array $arguments);
}
