# Woodhouse Changelog

### 0.3.2

* Fixed issue with publishing build-status and coverage images when using woodhouse via PHAR
* Publish command no-longer produces an error when there are no artifact changes to publish

### 0.3.1

* Updated to stable ezzatron/ci-status-images (3.0.1)
* Fixed case mismatch between PhpUnitTextReader and PHPUnitTextReader.php

### 0.3.0

* Renamed publish command from "github:publish" to "publish"
* Added support for publishing build status images (--build-status-* options)
* Added JUnit, TAP and PHPUnit JSON test report parsers
* Added support for different image themes via --image-theme
* Removed --fixed-width

### 0.2.0

* Added --message option
* Allowed relative paths as content sources
* Improved error reporting when content sources do not exist

### 0.1.0

* Initial release
