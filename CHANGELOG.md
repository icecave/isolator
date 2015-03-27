# Isolator Changelog

### 3.0.3 (2015-03-27)

* **[FIXED]** Generated code file is now created with the correct mode

### 3.0.2 (2015-03-23)

* **[FIXED]** Generated code directory is now created with the correct mode

### 3.0.1 (2014-10-21)

* **[FIXED]** References are now preserved when calling functions with variable arguments

### 3.0.0 (2014-10-08)

* **[BC]** Removed `Isolator::getIsolator()` and `resetIsolator()`
* **[BC]** Removed `Isolator::className()` (the full class name is now always `Icecave\Isolator\Isolator`)
* **[FIXED]** Calling functions with variable arguments now works correctly in PHP 5.6
* **[NEW]** Added `Isolator::set()`
* **[IMPROVED]** Code is now generated via a custom autoloader, and then cached, providing a massive performance improvement
* **[IMRPOVED]** Several micro-optimisations to invocation of function-like language constructs

While this release contains several backwards compatibility breaks, the `Isolator` class itself still behaves as per the
examples given in the README file of v2.3.0.

### 2.3.0 (2014-08-12)

* **[NEW]** Added support for isolation of the `new` operator

### 2.2.1 (2014-02-27)

* **[IMPROVED]** Updated autoloader to [PSR-4](http://www.php-fig.org/psr/psr-4/)

### 2.2.0 (2013-06-04)

* **[NEW]** Added `IsolatorTrait` to easily add an isolator dependency to a class
* **[NEW]** Added `Isolator::className()` method to get the actual class name of the generated isolator
* **[IMPROVED]** Several minor improvements to PSR compliance, documentation and test coverage

### 2.1.2 (2013-04-04)

* **[FIXED]** Return values of isolated `include/require[_once]` calls are now propagated correctly
* **[NEW]** Integrated `icecave/archer` (previously `icecave/testing`)

### 2.1.1 (2013-01-13)

* **[NEW]** Integrated `icecave/testing`
* **[IMPROVED]** Improved documentation
* **[IMPROVED]** Improved PSR compliance

### 2.1.0 (2012-08-12)

* **[NEW]** Added support for isolation of inclusion directives (`include/require[_once]`)

### 2.0.0 (2012-08-04)

* **[BC]** Changed vendor namespace from `IcecaveStudios` to `Icecave`
* **[BC]** Changed composer package name from `IcecaveStudios/isolator` to `icecave/isolator`

### 1.0.1 (2012-07-29)

* **[IMPROVED]** Re-organised directory layout to be consistent with other Composer projects

### 1.0.0 (2012-07-09)

* Initial release
