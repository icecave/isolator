<?php
namespace Icecave\Isolator\Detail;

use Icecave\Isolator\Isolator;
use ReflectionClass;

/**
 * @internal
 */
abstract class AbstractIsolator
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
    public function __call($name, array $arguments)
    {
        switch ($name) {
            case 'exit':
            case 'die':
                // @codeCoverageIgnoreStart
                exit($arguments[0]);
                // @codeCoverageIgnoreEnd
            case 'echo':
                echo $arguments[0];

                return;
            case 'eval':
                return eval($arguments[0]);
            case 'include':
                return include $arguments[0];
            case 'include_once':
                return include_once $arguments[0];
            case 'require':
                return require $arguments[0];
            case 'require_once':
                return require_once $arguments[0];
            case 'new':
                if (1 === \count($arguments)) {
                    return new $arguments[0]();
                }

                $reflector = new ReflectionClass(
                    \array_shift($arguments)
                );

                return $reflector->newInstanceArgs($arguments);
        }

        if ($arguments) {
            return \call_user_func_array($name, $arguments);
        }

        return $name();
    }

    /**
     * Fetch an isolator instance.
     *
     * This convenience method returns the global isolator instance, or $instance if provided.
     *
     * @param Isolator|null $isolator An existing isolator instance, if available.
     *
     * @return Isolator The global isolator instance, or $isolator if provided.
     */
    public static function get(Isolator $isolator = null)
    {
        if (null !== $isolator) {
            return $isolator;
        } elseif (!self::$instance) {
            self::$instance = new Isolator();
        }

        return self::$instance;
    }

    /**
     * Set the default isolator instance.
     *
     * @param Isolator $isolator The isolator instance.
     */
    public static function set(Isolator $isolator = null)
    {
        self::$instance = $isolator;
    }

    private static $instance;
}
