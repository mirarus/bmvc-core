<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use BMVC\Core\{App, Route, Controller};
use BMVC\Libs\{MError, Benchmark};

class Main {
	function index() {
		echo "[Main::index]";
	}
}

Route::any('/', function () {
	Controller::call('main@index');

	MError::color("info")->print("Benchmark", Benchmark::memory(true));
});

App::Run([
	'init' => [
		# BMVC\Core\Model::class
	]
]);