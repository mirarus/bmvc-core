<?php

require_once 'Libraries/Dir.php';

use BMVC\Libs\Dir;

require_once Dir::app('/vendor/autoload.php');


use BMVC\Core\{App, Route};
use BMVC\Libs\MError;

Route::any('/', function () {
	MError::color("info")::print("CLI");
});

App::Run();