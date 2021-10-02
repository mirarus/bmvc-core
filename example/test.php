<?php

require_once __DIR__ . '/vendor/autoload.php';

use BMVC\Core\App;
use BMVC\Libs\Route;
use BMVC\Core\Controller;
use BMVC\Libs\MError;
use BMVC\Libs\Benchmark;

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