# Overview

**Isolator** is a very simple shim class that provides an object-based access mechanism to global functions.
Any method called on an isolator instance is proxied on to the global function of the same name.

By using this object based approach instead of calling global functions directly global function calls
can be mocked just like any other method.

The isolator concept came into existence as a method to easy testing
in large projects that make heavy use of [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection).

## Example

*The following example assumes that you are familiar with principals of Dependency Injection, Unit Testing and Mock Objects.*

```php
<?php
class MyDocument {

  public function __construct($filename) {
    $this->filename = $filename;
  }
  
  public function getContents() {
    return file_get_contents($this->filename);
  }

  protected $filename;

}
```

The example class given performs some very simple filesystem operations, but immediately
it is a little painful to write a unit test for this class.

In order to write a unit test for this class you need to have a file to read.
An obvious approach might be to bundle some static fixtures on disk, or to make a temporary filesystem
when the test suite is set up, or even to use a virtual filesystem stream wrapper.

**Isolator** gives you a simple alternative at the cost of a little extra code.
Below is the class rewritten with **Isolator** support.

```php
<?php
use IcecaveStudios\Isolator\Isolator;

class MyDocument {

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

The first thing to note is that the the MyDocument class now accepts an Isolator instance
as a dependency, with a default of NULL, allowing for the Isolator::get() method to return
a reference to the global Isolator instance if no isolator is specified. The public interface
of the class is otherwise unchanged.

Secondly, the call to file_get_contents() is now made via the isolator instance.
The default behavior of the isolator is to proxy this call directly to the global function of the same name.

Finally, a simple unit test (written in PHPUnit with Phake) illustrates mocking the isolator methods.

```php
<?php

class MyDocumentTest extends PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->isolator = Phake::mock('IcecaveStudios\Isolator\Isolator');
    $this->myDocument = new MyDocument('foo.txt', $this->isolator);
  }
  
  public function testGetContents() {
    Phake::when($this->isolator)
      ->file_get_contents('foo.txt')
      ->thenReturn('This is the file contents.');
    
    $contents = $this->myDocument->getContents();
    $this->assertEquals($contents, 'This is the file contents.');
    
    Phake::verify($this->isolator)
      ->file_get_contents('foo.txt');
  }
  
}
```

MyDocument's behavior is completely covered by the unit test without requiring any actual disk access!

Using an isolator is most helpful when testing code that uses functions that rely on or manipulate global
state or an external resource, such as clock based functions, filesystem operations, databases, CURL, etc.

Hopefully it can help you improve the test coverage on your own PHP projects!
