<?php
namespace Icecave\Isolator\Detail;

use Icecave\Isolator\Isolator;
use Icecave\Isolator\IsolatorInterface;
use ReflectionClass;

/**
 * @internal
 */
abstract class AbstractIsolator implements IsolatorInterface
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
                exit(current($arguments));
                // @codeCoverageIgnoreEnd
            case 'echo':
                echo current($arguments);

                return;
            case 'eval':
                return eval(current($arguments));
            case 'include':
                return include current($arguments);
            case 'include_once':
                return include_once current($arguments);
            case 'require':
                return require current($arguments);
            case 'require_once':
                return require_once current($arguments);
            case 'new':
                $reflector = new ReflectionClass(
                    array_shift($arguments)
                );

                return $reflector->newInstanceArgs($arguments);
        }

        return call_user_func_array($name, $arguments);
    }

    /**
     * Fetch an isolator instance.
     *
     * This convenience method returns the global isolator instance, or $instance if provided.
     *
     * @param IsolatorInterface|null $isolator An existing isolator instance, if available.
     *
     * @return IsolatorInterface The global isolator instance, or $isolator if provided.
     */
    public static function get(IsolatorInterface $isolator = null)
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
     * @param IsolatorInterface $isolator The isolator instance.
     */
    public static function set(IsolatorInterface $isolator = null)
    {
        self::$instance = $isolator;
    }

    private static $instance;
}
