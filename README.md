koharness - bootstrap a clean Kohana app for testing your modules
===================================================================
[![Build Status](https://travis-ci.org/kohana/koharness.svg?branch=master)](https://travis-ci.org/kohana/koharness)

koharness is a very simple package (read, hack) that provides a clean testing environment for
your Kohana modules. It can generate a basic Kohana application containing your module and 
any dependencies, which you can then use to run tests with your favourite tools.

koharness gives you full control of module loading order, which may be vital for modules
that are designed to extend others using the Cascading File System.

## Installing koharness in your module

Add it as a development dependency in your module's composer.json:

```json
{
	"require-dev": {
		"kohana/koharness" : "*@dev"
	}
}
```

Run `composer install` in your module's root directory.

## Configuring module dependencies

Your module will almost certainly require the kohana core (if it doesn't you can probably 
test it in isolation without koharness or any other bootstrapper - which is obviously good).

If you want to use phpunit, you'll probably also want the Kohana unittest module.

Add them (and anything else you need) to your composer.json too. For extra cleanliness,
force composer-installers to install kohana modules into the normal vendor path instead 
of into /modules (note, this won't affect end-users of your module).

```json
{
	"require": {
		"kohana/core":     "3.3.*",
	},
	"require-dev": {
		"kohana/unittest": "3.3.*",
		"kohana/koharness" : "*@dev"
	},
	"extra": {
		"installer-paths": {
		"vendor/{$vendor}/{$name}": ["type:kohana-module"]
	}
  }
}
```

This will get all the dependencies installed on your machine, but you need to tell koharness how
to bootstrap them for your test environment and the order that they should be loaded by 
Kohana::modules().

Add a `koharness.php` file in the repository root:

```php
// {my-module-root}/koharness.php
return array(
	// This list of paths will also be passed to Kohana::modules(). Define the modules (including your own)
	// in the order you want them to appear in Kohana::modules()
	'modules' => array(
		'my-module' => __DIR__,
		'unittest'  => __DIR__.'/vendor/kohana/unittest'
	),

	// You can specify where to create the harness application - the default is /tmp/koharness
	'temp_dir' => '/home/me/testing'
);
```

## Building your harness

Now you have your module in a directory with all its dependencies inside it - so it needs to be
turned "inside out" to be more like a standard app with your module as a dependency. Here's where
koharness does it's thing.

In your module root directory, just run `vendor/bin/koharness` (presuming you're using composer's default 
`bin-dir` setting). This will **wipe out your specified temp_dir** and build a structure of symlinks and 
files that looks like this:

```
$temp_dir
\---application
|   \---cache
|   \---logs
|   |   bootstrap.php
|
\---modules
|   \---my-module -> WORKING_DIR
|   \---unittest  -> WORKING_DIR/vendor/kohana/unittest
|
\---system -> WORKING_DIR/vendor/kohana/core
\---vendor -> WORKING_DIR/vendor
```

The bootstrap.php is generated from a standard Kohana 3.3 template that ships with koharness, with your
modules included.

## Running tests

Once you have built your harness, you can run tests using whatever tool you prefer. 

### PHPUnit with the kohana unittest module

Easy peasy:

```shell
cd /tmp/koharness
vendor/bin/phpunit --bootstrap=modules/unittest/bootstrap.php modules/unittest/tests.php
```

### Other tools

If you want to load your kohana environment in phpspec, behat or similar then you'll also 
find your module working directory contains a `koharness_bootstrap.php` that defines the
various path constants that you'd normally get from index.php or the unittest module runner.

In phpspec for example, you could just do something like this:

```php
// MODULE_ROOT/spec/ObjectBehavior.php
<?php
namespace spec;
require_once(__DIR__.'/../koharness_bootstrap.php');
abstract class ObjectBehavior extends \PhpSpec\ObjectBehavior {}
```

```php
// MODULE_ROOT/spec/You/YourClassSpec.php
<?php
class YourClassSpec extends \spec\ObjectBehavior
```

## Configuring for Travis

To automate builds for your module on travis, you'd just do something like this:

```yml
language: php
install:
  - composer install
  - vendor/bin/koharness
  
script: vendor/bin/phpunit --bootstrap=modules/unittest/bootstrap.php modules/unittest/tests.php
```

## License

koharness is copyright (c) 2014 Kohana Team and distributed under a [BSD License](LICENSE.md).

It was contributed to the Kohana project by [inGenerator Ltd](http://www.ingenerator.com).
