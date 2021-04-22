<?php

require_once __DIR__ . '/vendor/autoload.php';

use BMVC\Core\{App, Route, Controller};
use BMVC\Libs\{MError, Benchmark};

class Main {
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