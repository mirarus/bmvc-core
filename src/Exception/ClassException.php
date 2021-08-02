<?php

/**
 * Class Exception
 *
 * Mirarus BMVC
 * @package BMVC\Exception
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.1
 */

namespace BMVC\Exception;

use Exception;
use BMVC\Libs\{CL, FS};

class ClassException extends Exception
{

	public function __construct($message)
	{
		$class = CL::replace(str_replace([FS::app(), '.php'], null, self::getFile()));
		
		if (class_exists($class, false)) {
		
			$class = CL::replace(str_replace(['BMVC\\Core', 'BMVC\\Libs'], null, $class));
			$message = '(' . $class . ') Error! | ' . $message;
		}

		parent::__construct($message);
	}
}