# Isolator Changelog

### 2.2.1 (2014-02-27)

* **[IMPROVED]** Updated autoloader to [PSR-4](http://www.php-fig.org/psr/psr-4/)

### 2.2.0 (2013-06-04)

* **[NEW]** Added `IsolatorTrait` to easily add an isolator dependency to a class
* **[NEW]** Added `Isolator::className()` method to get the actual class name of the generated isolator
* **[IMPROVED]** Several minor improvements to PSR compliance, documentation and test coverage

### 2.1.2 (2013-04-04)

* **[FIX]** Return values of isolated `include/require[_once]` calls are now propagated correctly
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
