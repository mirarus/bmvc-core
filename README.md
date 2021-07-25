# Bmvc-core

Mirarus BMVC Core (Basic MVC Core)

[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/mirarus/bmvc-core?style=flat-square&logo=php)](https://packagist.org/packages/mirarus/bmvc-core)
[![Packagist Version](https://img.shields.io/packagist/v/mirarus/bmvc-core?style=flat-square&logo=packagist)](https://packagist.org/packages/mirarus/bmvc-core)
[![Packagist Downloads](https://img.shields.io/packagist/dt/mirarus/bmvc-core?style=flat-square&logo=packagist)](https://packagist.org/packages/mirarus/bmvc-core)
[![Packagist License](https://img.shields.io/packagist/l/mirarus/bmvc-core?style=flat-square&logo=packagist)](https://packagist.org/packages/mirarus/bmvc-core)
[![PHP Composer](https://img.shields.io/github/workflow/status/mirarus/bmvc-core/PHP%20Composer/main?style=flat-square&logo=php)](https://github.com/mirarus/bmvc-core/actions/workflows/php.yml)

Libraries: [BMVC Libs](https://github.com/mirarus/bmvc-libs)

## Installation

Install using composer:

```bash
composer require mirarus/bmvc-core
```

## Example

Install using composer:

```bash
<?php

	require_once __DIR__ . '/vendor/autoload.php';

	use BMVC\Core\{App, Route, Controller};
	use BMVC\Libs\{MError, Benchmark};

	class Main
	{
		function index() {
			echo "[Main::index]";
		}
	}

	Route::any('/', function () {
		Controller::call('main@index');
		MError::color("info")::print("Benchmark", "Memory Usage: " . Benchmark::memory());
	});

	App::Run([
	'init' => [
	//BMVC\Core\Model::class
	]
]);
?>
```

## License

Licensed under the MIT license, see [LICENSE](LICENSE)
