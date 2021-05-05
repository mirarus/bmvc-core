<?php

require_once __DIR__ . '/vendor/autoload.php';

use BMVC\Core\App;
use BMVC\Core\Route;
use BMVC\Core\Controller;
# use BMVC\Core\{App, Route, Controller};
use BMVC\Libs\MError;
use BMVC\Libs\Benchmark;
# use BMVC\Libs\{MError, Benchmark};

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