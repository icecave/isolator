# Isolator

[![Build Status]](https://travis-ci.org/IcecaveStudios/isolator)
[![Test Coverage]](https://coveralls.io/r/IcecaveStudios/isolator?branch=develop)
[![SemVer]](http://semver.org)

**Isolator** simplifies testing of classes that make use of global functions by treating all global functions as methods
on an "isolator" object.

* Install via [Composer](http://getcomposer.org) package [icecave/isolator](https://packagist.org/packages/icecave/isolator)
* Read the [API documentation](http://icecavestudios.github.io/isolator/artifacts/documentation/api/)

## Rationale

A large number of PHP extensions (and the PHP core) implement their functionality as global functions. Testing classes
that use these functions quickly becomes difficult due to the inability to replace them with [test doubles](http://en.wikipedia.org/wiki/Test_double).

**Isolator** endeavours to solve this problem by acting as a proxy between your class and global functions. An isolator
instance is passed into your object as a [dependency](http://en.wikipedia.org/wiki/Dependency_injection) and used in
place of any global function calls that you may want to replace when testing.

## Example

The following class makes use of [file_get_contents()](http://php.net/manual/en/function.file-get-contents.php) to read
the contents of a file.

```php
class MyDocument
{
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function getContents()
    {
        return file_get_contents($this->filename);
    }

    protected $filename;
}
```

Despite the simplicity of the example, the class immediately becomes difficult to test due to it's reliance on the
filesystem. In order to test this class you might be inclined to set up some static fixtures on disk, make a temporary
directory when your test suite is set up or perhaps even use a [virtual filesystem wrapper](http://code.google.com/p/bovigo/wiki/vfsStream).

**Isolator** provides a fourth alternative. Given below is the same example rewritten using an `Isolator` instance.

```php
use Icecave\Isolator\Isolator;

class MyDocument
{
    public function __construct($filename, Isolator $isolator = null)
    {
        $this->filename = $filename;
        $this->isolator = Isolator::get($isolator);
    }

    public function getContents()
    {
        return $this->isolator->file_get_contents($this->filename);
    }

    protected $filename;
    protected $isolator;
}
```

`MyDocument` now takes an instance of `Isolator` in it's constructor. It would be a pain - and unnecessary - to create a
new `Isolator` instance every time you construct an object in your production code, so a shared instance is made
accessible using the `Isolator::get()` method. If a non-null value is passed to `Isolator::get()` it is returned
unchanged, allowing you to replace the isolator when necessary.

`MyDocument::getContents()` is also updated to use the isolator instance rather than calling the global function
directly. The behavior of `MyDocument` remains unchanged but testing the class is easy, as will be shown in the example
test suite below.

*Note: The test below is written for the [PHPUnit](http://www.phpunit.de) testing framework, using [Phake](https://github.com/mlively/Phake)
for mocking. Phake provides a more flexible alternative to PHPUnit's built-in mock objects.*

```php
class MyDocumentTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // First a mocked isolator instance is created ...
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');

        // That isolator instance is given to the MyDocument instance
        // that is to be tested ...
        $this->myDocument = new MyDocument('foo.txt', $this->isolator);
    }

    public function testGetContents()
    {
        // Phake is used to configure the mocked isolator to return a known
        // string when file_get_contents() is called with a parameter equal
        // to 'foo.txt' ...
        Phake::when($this->isolator)
          ->file_get_contents('foo.txt')
          ->thenReturn('This is the file contents.');

        // MyDocument::getContents() is called, and it's result checked ...
        $contents = $this->myDocument->getContents();
        $this->assertEquals($contents, 'This is the file contents.');

        // Finally Phake is used to verify that a call to file_get_contents()
        // was made as expected ...
        Phake::verify($this->isolator)
          ->file_get_contents('foo.txt');
    }
}
```

The test verifies the behavior of the `MyDocument` class completely, without requiring any disk access.

Using an isolator is most helpful when testing code that uses functions which maintain global state or utilize external
resources such as databases, filesystems, etc. It is usually unnecessary to mock out deterministic functions such as
`strlen()`, for example.

## Isolator Trait

In PHP 5.4 and later, it is also possible to use `IsolatorTrait` to bring an isolator into your class. The isolator
instance is accessed using `$this->isolator()` and can be set via `$this->setIsolator()`.

```php
use Icecave\Isolator\IsolatorTrait;

class MyDocument
{
    use IsolatorTrait;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function getContents()
    {
        return $this->isolator()->file_get_contents($this->filename);
    }

    protected $filename;
}
```

## Language Constructs

**Isolator** can also be used to invoke the following function-like language constructs:

 * `include`, `include_once`, `require` and `require_once`
 * `exit` and `die`
 * `echo`
 * `eval`
 * `new`

## Peculiarities

Several of PHP's core global functions have some peculiarities and inconsitencies in the way they are defined.
**Isolator** attempts to accomodate such inconsistencies when possible, but may have issues with some native C functions
for which parameter reflection information is non-standard or incorrect. These issues seem to be largely rectified as of
PHP 5.6.

## Contact us

* Follow [@IcecaveStudios](https://twitter.com/IcecaveStudios) on Twitter
* Visit the [Icecave Studios website](http://icecave.com.au)
* Join `#icecave` on [irc.freenode.net](http://webchat.freenode.net?channels=icecave)

<!-- references -->
[Build Status]: http://img.shields.io/travis/IcecaveStudios/isolator/develop.svg?style=flat-square
[Test Coverage]: http://img.shields.io/coveralls/IcecaveStudios/isolator/develop.svg?style=flat-square
[SemVer]: http://img.shields.io/:semver-3.0.3-brightgreen.svg?style=flat-square
