<?php
namespace Icecave\Isolator\Detail;

use Exception;
use Icecave\Isolator\PackageInfo;

/**
 * An arcane magic autoloader that generates code for the isolator class when it
 * is first loaded.
 */
class Autoloader
{
    /**
     * @param string        $path          The path under which to store generated code.
     * @param CodeGenerator $codeGenerator The code generator used to create isolator classes.
     */
    public function __construct($path, CodeGenerator $codeGenerator)
    {
        $this->path          = $path;
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Load an isolator class with the given name.
     *
     * @param string $className The name to give to the isolator class.
     */
    public function load($className)
    {
        $functions = get_defined_functions();
        $functions = $functions['internal'];

        $hash = $this->computeHash($className, $functions);

        $fileName = $this->path . DIRECTORY_SEPARATOR . 'Isolator' . $hash . '.php';

        if (!file_exists($fileName)) {
            $this->generateIsolatorClass(
                $fileName,
                $className,
                $functions
            );
        }

        require_once $fileName;
    }

    /**
     * Compute a unique hash used to identify an isolator implementation that
     * proxies the given functions.
     *
     * @param string        $className The name to give to the isolator class.
     * @param array<string> $functions An array of global function names.
     *
     * @return string
     */
    public function computeHash($className, array $functions)
    {
        $hash = hash_init('sha1');

        hash_update($hash, $className);

        foreach ($functions as $function) {
            hash_update($hash, $function);
        }

        // Include the PHP version in the hash in case function signatures
        // change between versions.
        hash_update($hash, phpversion());

        // Include the Isolator version in the hash in case code generation
        // changes between versions.
        hash_update($hash, PackageInfo::VERSION);

        return hash_final($hash);
    }

    /**
     * Generate and store an isolator implementation.
     *
     * @param string        $className The name to give to the isolator class.
     * @param array<string> $functions An array of global function names.
     */
    public function generateIsolatorClass(
        $fileName,
        $className,
        array $functions
    ) {
        $dirName = dirname($fileName);
        $umask   = umask(0);

        try {
            if (!file_exists($dirName)) {
                mkdir($dirName, 0777, true);
            }

            $code = $this
                ->codeGenerator
                ->generate(
                    $className,
                    $functions
                );

            file_put_contents($fileName, $code);

            chmod($fileName, 0644);

            umask($umask);
        } catch (Exception $e) {
            umask($umask);

            throw $e;
        }
    }

    private $path;
    private $codeGenerator;
}
