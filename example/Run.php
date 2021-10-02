<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use BMVC\Core\App;
use BMVC\Libs\{Route, MError};

Route::any('/', function () {
	MError::color("info")::print("CLI");
});

App::Run();