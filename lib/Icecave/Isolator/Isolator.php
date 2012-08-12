<?php
namespace Icecave\Isolator;

use ReflectionFunction;

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
     * @param string $name The name of the global function to call.
     * @param array $arguments The arguments to the function.
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
                include current($arguments);
                return;
            case 'include_once':
                include_once current($arguments);
                return;
            case 'require':
                require current($arguments);
                return;
            case 'require_once':
                require_once current($arguments);
                return;
            default:

        }

        return call_user_func_array($name, $arguments);
    }

    /**
     * Fetch a usable isolator instance, or return an existing one.
     *
     * If no instance is provided, a new instance will be created and returned.
     *
     * @param Isolator|NULL $instance An existing isolator instance, if available.
     *
     * @return Isolator A new isolator instance, or the provided instance if available.
     */
    public static function get(Isolator $instance = NULL)
    {
        if ($instance) {
            return $instance;
        }

        return static::getIsolator();
    }

    /**
     * Fetch the default isolator instance, constructing it if necessary.
     *
     * @param boolean $handleReferences Indicates whether or not the isolator should account for functions with reference parameters and return types.
     * @param Generator|NULL $generator The Generator instance to use to construct the concreate isolator class, or NULL to use the default.
     * @param Isolator|NULL $isolator The isolator used to access the global list of functions, or NULL to use the default.
     */
    public static function getIsolator($handleReferences = TRUE, Generator $generator = NULL, Isolator $isolator = NULL)
    {
        // Global instance already initialized ...
        if (self::$instance !== NULL) {
            return self::$instance;
        }

        // No need to handle references, rely on default Isolator::__call() method ...
        if (!$handleReferences) {
            return self::$instance = new self;
        }

        // Construct an isolator generator to create the concreate isolator class ...
        if ($generator === NULL) {
            $generator = new Generator;
        }

        // Get a basic isolator to use for reflection ...
        if ($isolator === NULL) {
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
        self::$instance = NULL;
    }

    private static $instance;
}
