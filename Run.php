<?php

require_once __DIR__ . '/vendor/autoload.php';

use BMVC\Core\{App, Route};
use BMVC\Libs\MError;

Route::any('/', function () {
	MError::color("info")::print("CLI");
});

App::Run();