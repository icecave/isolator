<?php
namespace Icecave\Isolator;

use ReflectionClass;
use ReflectionFunction;

/**
 * Generates an isolator that can accommodate calls to functions with reference parameters.
 */
class Generator
{
    public function __construct($ellipsisExpansion = 10, Isolator $isolator = null)
    {
        $this->ellipsisExpansion = $ellipsisExpansion;
        $this->isolator = $isolator ?: new Isolator; // Note, Isolator::getIsolator is not used to avoid recursion.
    }

    public function generateClass(array $functionReflectors, $className = null, $baseClassName = 'Isolator')
    {
        if ($className === null) {
            $className = 'Isolator' . self::$count++;
        }

        $code    = 'namespace ' . __NAMESPACE__ . ' {' . PHP_EOL;
        $code .= 'class ' . $className . ' extends ' . $baseClassName . ' {' . PHP_EOL;
        $code .= PHP_EOL;

        foreach ($functionReflectors as $reflector) {
            if ($this->requiresIsolatorProxy($reflector)) {
                $code .= $this->generateProxyMethod($reflector);
                $code .= PHP_EOL;
            }
        }

        $code .= '} // End class.' . PHP_EOL;
        $code .= '} // End namespace.' . PHP_EOL;

        $this->isolator->eval($code);

        return new ReflectionClass(__NAMESPACE__ . '\\' . $className);
    }

    public function requiresIsolatorProxy(ReflectionFunction $reflector)
    {
        if ($reflector->isDisabled()) {
            return false;
        } elseif ($reflector->returnsReference()) {
            return true;
        }

        foreach ($reflector->getParameters() as $parameter) {
            if ($parameter->isPassedByReference()) {
                return true;
            }
        }

        return false;
    }

    public function inspect(ReflectionFunction $reflector)
    {
        $minArity = 0;
        $maxArity = 0;
        $refIndices = array();

        foreach ($reflector->getParameters() as $parameter) {
            $refIndices[$maxArity++] = $parameter->isPassedByReference();

            if (!$parameter->isOptional()) {
                ++$minArity;
            }

            if ($parameter->getName() === '...') {
                $ref = $parameter->isPassedByReference();
                for ($count = 1; $count < $this->ellipsisExpansion; ++$count) {
                    $refIndices[$maxArity++] = $ref;
                }
                break;
            }
        }

        return array($minArity, $maxArity, $refIndices);
    }

    public function generateProxyMethod(ReflectionFunction $reflector)
    {
        list($minArity, $maxArity, $refIndices) = $this->inspect($reflector);
        $name = $reflector->getName();
        $code    = $this->generateSignature($name, $reflector->returnsReference(), $minArity, $maxArity, $refIndices) . ' {' . PHP_EOL;
        $code .= $this->generateSwitch($name, $minArity, $maxArity);
        $code .= $this->generateReturn($name, $maxArity);
        $code .= '} // End function ' . $name . '.' . PHP_EOL;
        $code .= PHP_EOL;

        return $code;
    }

    protected function generateSignature($name, $returnsReference, $minArity, $maxArity, $refIndices)
    {
        $parameters = array();
        for ($index = 0; $index < $maxArity; ++$index) {
            $param = '';
            if ($refIndices[$index]) {
                $param .= '&';
            }
            $param .= '$_' . $index;
            if ($index >= $minArity) {
                $param .= ' = null';
            }
            $parameters[] = $param;
        }

        return sprintf(
            'public function %s%s(%s)'
            , $returnsReference ? '&' : ''
            , str_replace('\\', '_', $name)
            , implode(', ', $parameters)
        );
    }

    protected function generateSwitch($name, $minArity, $maxArity)
    {
        if ($minArity === $maxArity) {
            return '';
        }

        $code    = 'switch (func_num_args()) {' . PHP_EOL;

        for ($arity = $minArity; $arity < $maxArity; ++$arity) {
            $code .= sprintf(
                'case %d: %s'
                , $arity
                , $this->generateReturn($name, $arity)
            );
        }

        $code .= '} // End switch.' . PHP_EOL;

        return $code;
    }

    protected function generateReturn($name, $arity)
    {
        $arguments = array();
        for ($index = 0; $index < $arity; ++$index) {
            $arguments[] = '$_' . $index;
        }

        return sprintf(
            'return \%s(%s);' . PHP_EOL
            , $name
            , implode(', ', $arguments)
        );
    }

    private static $count = 0;
    protected $ellipsisExpansionDepth;
    protected $isolator;
}
