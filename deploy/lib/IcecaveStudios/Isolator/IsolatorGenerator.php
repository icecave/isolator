<?php
namespace IcecaveStudios\Isolator;

use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;

/**
 * Generates an isolator that can accommodate calls to functions with reference parameters.
 */
class IsolatorGenerator {

  public function generate(array $functions) {
    $name = sprintf('ConcreteIsolator_%08x', time());
    $code = sprintf(
      'namespace %s { final class %s extends Isolator {' . PHP_EOL
      , __NAMESPACE__
      , $name
    );

    foreach ($functions as $function) {
      if ($method = $this->generateOverride($function)) {
        $code .= $method;
      }
    }
    $code .= '} }' . PHP_EOL;

    eval($code);
    return new ReflectionClass(__NAMESPACE__ . '\\' . $name);
  }
  
  public function generateOverride(ReflectionFunction $function) {

    // Whether or not to generate the method override or not.
    // Only required when references are present or variable
    // arity is handled in a non-native way.
    $overrideRequired = $function->returnsReference();

    // Indicates that the function has "magic" defaults.
    // optional parameters for reflection can provide no default value/
    $hasMagicDefaults = FALSE;

    $parameters = array();
    $arguments = array();

    foreach ($function->getParameters() as $parameter) {
      if ($parameter->getName() === '...') {
        return NULL; // Some PHP functions have unusual variable arity definitions, these are not supported.
      } else if ($parameter->isOptional() and !$parameter->isDefaultValueAvailable()) {
        $hasMagicDefaults = $overrideRequired = TRUE;
      } else if ($parameter->isPassedByReference()) {
        $overrideRequired = TRUE;
      }
      $arguments[] = '$' . $parameter->getName();
      $parameters[] = $this->exportParameter($parameter);
    }
    
    // No override is required at all, do not generate any code ...
    if (!$overrideRequired) {
      return NULL;
    }
    
    $code = sprintf(
      '  public function %s%s(%s) {' . PHP_EOL
      , $function->returnsReference() ? '&' : ''
      , $function->getName()
      , implode(', ', $parameters)
    );

    if ($hasMagicDefaults) {
      $min = $function->getNumberOfRequiredParameters();
      $max = $function->getNumberOfParameters();
      $code .= '    switch (func_num_args()) {' . PHP_EOL;
      for ($arity = $min; $arity < $max; ++$arity) {
        $code .= sprintf(
          '      case %d: return %s(%s);' . PHP_EOL
          , $arity
          , $function->getName()
          , implode(', ', array_slice($arguments, 0, $arity))
        );
      }
      $code .= '    }' . PHP_EOL;
    }

    $code .= sprintf(
      '    return %s(%s);' . PHP_EOL
      , $function->getName()
      , implode(', ', $arguments)
    );

    $code .= '  }' . PHP_EOL;

    return $code;
  }
  
  public function exportParameter(ReflectionParameter $parameter) {

    // Build the type hint ...
    if ($parameter->isArray()) {
      $type_hint = 'array';
    } else if ($class = $parameter->getClass()) {
      $type_hint = $class->getName();
    } else {
      $type_hint = '';
    }
    
    // Build the default value ...
    if ($parameter->isDefaultValueAvailable()) {
      $default .= '= ' . var_export($parameter->getDefaultValue(), TRUE);
    } else if ($parameter->isOptional() or ($parameter->allowsNull() and $type_hint)) {
      $default = '= NULL';
    } else {
      $default = '';
    }
    
    $reference = $parameter->isPassedByReference() ? '&' : '';

    return trim($type_hint . ' ' . $reference . '$' . $parameter->getName() . ' ' . $default);
  }
  
}