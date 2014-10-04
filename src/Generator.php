<?php
namespace Icecave\Isolator;

use ReflectionClass;
use ReflectionFunction;

/**
 * Generates an isolator that can accommodate calls to functions with reference parameters.
 *
 * @internal
 */
class Generator
{
    public function __construct($ellipsisExpansion = 10)
    {
        $this->ellipsisExpansion = $ellipsisExpansion;
    }

    public function generate($useCache = true)
    {
        $hash = hash_init('md5');
        hash_update($hash, phpversion());
        hash_update($hash, __FILE__);

        $allFunctions = array();

        foreach (get_defined_functions() as $type => $functions) {
            hash_update($hash, $type);

            foreach ($functions as $function) {
                hash_update($hash, $function);
                $allFunctions[] = $function;
            }
        }

        $className     = 'Isolator_' . hash_final($hash);
        $qualifiedName = __NAMESPACE__ . '\\' . $className;

        if (class_exists($qualifiedName, false)) {
            return new ReflectionClass($qualifiedName);
        }

        $directory = sys_get_temp_dir();
        $fileName  = $directory . DIRECTORY_SEPARATOR . $className . '.php';

        if ($useCache) {
            @include $fileName;
            $useCache = class_exists($qualifiedName, false);
        }

        if (!$useCache) {
            $code = $this->generateClass($className, $allFunctions);
            file_put_contents($fileName, $code);
            include $fileName;
        }

        return new ReflectionClass($qualifiedName);
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

    public function generateClass($className, array $functions)
    {
        $code  = '<?php' . PHP_EOL;
        $code .= 'namespace ' . __NAMESPACE__ . ';' . PHP_EOL;
        $code .= PHP_EOL;
        $code .= 'class ' . $className . ' extends Isolator' . PHP_EOL;
        $code .= '{' . PHP_EOL;
        foreach ($functions as $function) {
            $reflector = new ReflectionFunction($function);
            if ($this->requiresIsolatorProxy($reflector)) {
                $code .= $this->generateProxyMethod($reflector);
            }
        }
        $code .= '}' . PHP_EOL;

        return $code;
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

    protected function generateProxyMethod(ReflectionFunction $reflector)
    {
        $name = $reflector->getName();

        list($minArity, $maxArity, $refIndices) = $this->inspect($reflector);

        $signature = $this->generateSignature(
            $name,
            $reflector->returnsReference(),
            $minArity,
            $maxArity,
            $refIndices
        );

        $code  = $signature . PHP_EOL;
        $code .= '{' . PHP_EOL;
        $code .= $this->generateSwitch($name, $minArity, $maxArity);
        $code .= $this->generateReturn($name, $maxArity);
        $code .= '}' . PHP_EOL;
        $code .= PHP_EOL;

        return $code;
    }

    protected function generateSignature(
        $name,
        $returnsReference,
        $minArity,
        $maxArity,
        $refIndices
    ) {
        $parameters = array();
        for ($index = 0; $index < $maxArity; ++$index) {
            $param = '';
            if ($refIndices[$index]) {
                $param .= '&';
            }
            $param .= '$p' . $index;
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

        $code = 'switch (func_num_args()) {' . PHP_EOL;

        for ($arity = $minArity; $arity < $maxArity; ++$arity) {
            $code .= sprintf(
                'case %d: %s'
                , $arity
                , $this->generateReturn($name, $arity)
            );
        }

        $code .= '}' . PHP_EOL;

        return $code;
    }

    protected function generateReturn($name, $arity)
    {
        $arguments = array();
        for ($index = 0; $index < $arity; ++$index) {
            $arguments[] = '$p' . $index;
        }

        return sprintf(
            'return \%s(%s);' . PHP_EOL
            , $name
            , implode(', ', $arguments)
        );
    }

    protected $ellipsisExpansion;
}
