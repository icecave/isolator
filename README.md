# Overview

[![Build Status](https://secure.travis-ci.org/IcecaveStudios/isolator.png)](http://travis-ci.org/IcecaveStudios/isolator)

**Isolator** is a small library for easing testing of classes that make use of global functions.

A large number of PHP extensions (and the PHP core) still implement their functionality in global functions.
Testing classes that use these functions quickly becomes difficult due to the inability to replace them with [test doubles](http://en.wikipedia.org/wiki/Test_double).

**Isolator** endeavours to solve this problem by acting as a proxy between your class and global functions.
An isolator instance is passed into your object as [dependency](http://en.wikipedia.org/wiki/Dependency_injection) and
used in place of any global function calls that you may want to replace when testing.

## Installation

**Isolator** requires PHP 5.3.

### With [Composer](http://getcomposer.org/)

* Add 'icecave/isolator' to the project's composer.json dependencies
* Run `php composer.phar install`

### Bare installation

* Clone from GitHub: `git clone git://github.com/IcecaveStudios/isolator.git`
* Use a [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
  compatible autoloader (namespace 'Icecave\Isolator' in the 'lib' directory)

## Example

The following class makes use of [file_get_contents()](http://php.net/manual/en/function.file-get-contents.php) to read the contents of a file.

```php
<?php
class MyDocument
{
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function getContents() {
        return file_get_contents($this->filename);
    }

    protected $filename;
}
```

Despite the simplicity of the example the class immediately becomes difficult to test due to it's reliance on the filesystem.
In order to test this class you might be inclined to set up some static fixtures on disk, make a temporary directory when your test suite
is set up or perhaps even use a [virtual filesystem wrapper](http://code.google.com/p/bovigo/wiki/vfsStream).

**Isolator** provides a fourth alternative. Given below is the same example rewritten using an Isolator instance.

```php
<?php
use Icecave\Isolator\Isolator;

class MyDocument
{
    public function __construct($filename, Isolator $isolator = NULL) {
        $this->filename = $filename;
        $this->isolator = Isolator::get($isolator);
    }

    public function getContents() {
        return $this->isolator->file_get_contents($this->filename);
    }

    protected $filename;
    protected $isolator;
}
```

MyDocument now takes an instance of Isolator in it's constructor. It would a pain and unnecessary to specify the Isolator instance every time you construct an object in your production code, so a shared instance is made accessible using the Isolator::get() method. If a non-NULL value is passed to Isolator::get() it is returned unchanged, allowing you to replace the Isolator when necessary.

MyDocument::getContents() is also updated to use the isolator instance rather than calling the global function directly. The behavior of MyDocument remains unchanged but testing the class is easy, as will be shown in the example test suite below.

*Note: The test below is written for the [PHPUnit](http://www.phpunit.de) testing framework, using [Phake](https://github.com/mlively/Phake) for mocking. Phake provides a more flexible alternative to PHPUnit's built-in mock objects.*

```php
<?php
class MyDocumentTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // First a mocked isolator instance is created ...
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');

        // That isolator instance is provided to the MyDocument instance that is to be tested ...
        $this->myDocument = new MyDocument('foo.txt', $this->isolator);
    }

    public function testGetContents()
    {
        // Phake is used to configure the mocked isolator to return a known string
        // when file_get_contents() is called with a parameter equal to 'foo.txt' ...
        Phake::when($this->isolator)
          ->file_get_contents('foo.txt')
          ->thenReturn('This is the file contents.');

        // MyDocument::getContents() is called, and it's result checked ...
        $contents = $this->myDocument->getContents();
        $this->assertEquals($contents, 'This is the file contents.');

        // Finally Phake is used to verify that a call to file_get_contents() was made as expected ...
        Phake::verify($this->isolator)
          ->file_get_contents('foo.txt');
    }
}
```

The test verifies the behavior of the MyDocument class completely, without requiring any disk access.

Using an isolator is most helpful when testing code that uses global functions which maintain global state or utilize external resources such as databases, filesystems, etc. It is usually unnecessary to mock out deterministic functions such as strlen(), for example.

## Peculiarities

Several of PHP's core global functions have some peculiarities and inconsitencies in the way they are defined. **Isolator** attempts to accomodate such inconsistencies when possible, but at this point there has not been a great deal of testing of this functionality.
