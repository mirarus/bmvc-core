<?php

/**
 * INIT
 *
 * Mirarus BMVC
 * @package BMVC
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 2.4
 */

require_once ROOTDIR . '/vendor/autoload.php';

BMVC\Core\App::Run([
	'files' => [
		APPDIR . '/routes.php',
		APPDIR . '/config.php'
	]
]);