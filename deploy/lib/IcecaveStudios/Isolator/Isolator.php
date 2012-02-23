<?php
namespace IcecaveStudios\Isolator;

use ReflectionFunction;

/**
 * Isolates calls to global functions to ease mocking / unit testing.
 */
abstract class Isolator {

  /**
   * Get an isolator instance.
   *
   * If an isolator instance is passed it is returned unchanged.
   * If NULL is passed the isolator is assigned the value of the global Isolator instance.
   *
   * @param Isolator|NULL &$isolator The isolator to adapt, or NULL to use the global instance.
   *
   * @return Isolator The adapted isolator.
   */
  public static function get(Isolator &$isolator = NULL, IsolatorGenerator $generator = NULL) {

    // We already have an isolator ...
    if ($isolator !== NULL) {
      return $isolator;
    }

    // The global isolator has already been initialized ...
    if (self::$instance !== NULL) {
      return $isolator = self::$instance;
    }
    
    // Get a list of the global functions ...
    $functions = get_defined_functions();
    $functions = array_map(
      function($name) {
        return new ReflectionFunction($name);
      }
      , $functions['internal']
    );
    
    // Generate a concrete isolator class that handles functions with references ...
    $generator = $generator ?: new IsolatorGenerator;
    $reflector = $generator->generate($functions);
    return $isolator = self::$instance = $reflector->newInstance();
  }
  
  /**
   * Forward a call onto global function.
   *
   * @param string $name The name of the global function to call.
   * @param array $arguments The arguments to the function.
   *
   * @return mixed The result of the function call.
   */
  public function __call($name, array $arguments) {
    if ($name === 'exit' or $name === 'die') {
      exit($arguments[0]);
    } else if ($name === 'echo') {
      echo $arguments[0];
    } else {
      return call_user_func_array($name, $arguments);
    }
  }

  /**
   * @var Isolator The global isolator instance.
   */
  private static $instance;

}
