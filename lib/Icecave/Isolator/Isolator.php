<?php
namespace Icecave\Isolator;

use ReflectionFunction;

/**
 * Isolate calls to global PHP functions.
 */
class Isolator
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
            default:

        }

        return call_user_func_array($name, $arguments);
    }

    /**
     * Fetch an isolator instance.
     *
     * This convenience method returns the global isolator instance, or $instance if provided.
     *
     * @param Isolator|null $instance An existing isolator instance, if available.
     *
     * @return Isolator The global isolator instance, or $instance if provided.
     */
    public static function get(Isolator $instance = null)
    {
        if ($instance) {
            return $instance;
        }

        return static::getIsolator();
    }

    /**
     * Fetch the isolator class name.
     *
     * @return string The concrete class name for the global isolator instance.
     */
    public static function className()
    {
        return get_class(static::get());
    }

    /**
     * Fetch the default isolator instance, constructing it if necessary.
     *
     * @param boolean        $handleReferences Indicates whether or not the isolator should account for functions with reference parameters and return types.
     * @param Generator|null $generator        The Generator instance to use to construct the concreate isolator class, or null to use the default.
     * @param Isolator|null  $isolator         The isolator used to access the global list of functions, or null to use the default.
     */
    public static function getIsolator($handleReferences = true, Generator $generator = null, Isolator $isolator = null)
    {
        // Global instance already initialized ...
        if (self::$instance !== null) {
            return self::$instance;
        }

        // No need to handle references, rely on default Isolator::__call() method ...
        if (!$handleReferences) {
            return self::$instance = new self;
        }

        // Construct an isolator generator to create the concreate isolator class ...
        if ($generator === null) {
            $generator = new Generator;
        }

        // Get a basic isolator to use for reflection ...
        if ($isolator === null) {
            $isolator = new self;
        }

        // Create reflectors for each of the globally defined functions ...
        $functionReflectors = array();
        foreach ($isolator->get_defined_functions() as $functions) {
            foreach ($functions as $name) {
                $functionReflectors[] = new ReflectionFunction($name);
            }
        }

        // Generate the concrete isolator class and install it as the global instance ...
        $classReflector = $generator->generateClass($functionReflectors);

        return self::$instance = $classReflector->newInstance();
    }

    /**
     * Reset the default isolator instance.
     */
    public static function resetIsolator()
    {
        self::$instance = null;
    }

    private static $instance;
}
