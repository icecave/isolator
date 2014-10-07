<?php
namespace Icecave\Isolator\Detail;

use ReflectionFunction;

/**
 * Generates an isolator that can accommodate calls to functions with reference parameters.
 *
 * @internal
 */
class CodeGenerator
{
    public function __construct($ellipsisExpansion = 10)
    {
        $this->ellipsisExpansion = $ellipsisExpansion;
    }

    /**
     * Generate an isolator implementation.
     */
    public function generate($className, array $functions)
    {
        $pos = strrpos($className, '\\');

        if ($pos) {
            $namespace = substr($className, 0, $pos);
            $shortName = substr($className, $pos + 1);
        } else {
            $namespace = null;
            $shortName = $className;
        }

        $code  = '<?php' . PHP_EOL;

        if ($namespace) {
            $code .= 'namespace ' . $namespace . ';' . PHP_EOL;
        }

        $code .= PHP_EOL;
        $code .= 'use Icecave\Isolator\Detail\AbstractIsolator;' . PHP_EOL;
        $code .= PHP_EOL;
        $code .= 'class ' . $shortName . ' extends AbstractIsolator' . PHP_EOL;
        $code .= '{' . PHP_EOL;

        foreach ($functions as $function) {
            $code .= $this->generateMethod(
                new ReflectionFunction($function)
            );
        }

        $code .= '}' . PHP_EOL;

        return $code;
    }

    private function generateMethod(ReflectionFunction $reflector)
    {
        $name = $reflector->getName();

        list($minArity, $maxArity, $refIndices) = $this->inspectParameters($reflector);

        $signature = $this->generateSignature(
            $name,
            $reflector->returnsReference(),
            $minArity,
            $maxArity,
            $refIndices
        );

        $code  = '    ' . $signature . PHP_EOL;
        $code .= '    {' . PHP_EOL;
        $code .= $this->generateSwitch($name, $minArity, $maxArity);
        $code .= '        return \call_user_func_array(' . var_export($reflector->getName(), true) . ', \func_get_args());' . PHP_EOL;
        $code .= '    }' . PHP_EOL;

        return $code;
    }

    private function generateSignature(
        $name,
        $returnsReference,
        $minArity,
        $maxArity,
        $refIndices
    ) {
        $parameters = array();

        for ($index = 0; $index < $maxArity; ++$index) {
            $param = '$p' . $index;

            if ($refIndices[$index]) {
                $param = '&' . $param;
            }

            if ($index >= $minArity) {
                $param .= ' = null';
            }

            $parameters[] = $param;
        }

        return sprintf(
            'public function %s%s(%s)',
            $returnsReference ? '&' : '',
            str_replace('\\', '_', $name),
            implode(', ', $parameters)
        );
    }

    private function generateSwitch($name, $minArity, $maxArity)
    {
        $code = '        switch (\func_num_args()) {' . PHP_EOL;

        for ($arity = $minArity; $arity <= $maxArity; ++$arity) {
            $code .= sprintf(
                '            case %d: %s',
                $arity,
                $this->generateReturn($name, $arity)
            );
        }

        $code .= '        }' . PHP_EOL;

        return $code;
    }

    private function generateReturn($name, $arity)
    {
        $arguments = array();
        for ($index = 0; $index < $arity; ++$index) {
            $arguments[] = '$p' . $index;
        }

        return sprintf(
            'return \%s(%s);' . PHP_EOL,
            $name,
            implode(', ', $arguments)
        );
    }

    private function inspectParameters(ReflectionFunction $reflector)
    {
        $minArity = 0;
        $maxArity = 0;
        $refIndices = array();

        foreach ($reflector->getParameters() as $parameter) {
            // PHP versions < 5.6 showed a parameter named '...' to indicate
            // that a function took an arbitrary number of arguments.
            //
            // @codeCoverageIgnoreStart
            if ($parameter->getName() === '...') {
                break;
            }
            // @codeCoverageIgnoreEnd

            $refIndices[$maxArity++] = $parameter->isPassedByReference();

            if (!$parameter->isOptional()) {
                ++$minArity;
            }
        }

        return array($minArity, $maxArity, $refIndices);
    }

    private $ellipsisExpansion;
}
