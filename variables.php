<?php

/**
 * VERIABLES
 *
 * Mirarus BMVC
 * @package BMVC
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.1
 */

if (is_string(config('general/timezone'))) {
	define("TIMEZONE", config('general/timezone'));
}

if (is_string(config('general/environment'))) {
	define("ENVIRONMENT", config('general/environment'));
}