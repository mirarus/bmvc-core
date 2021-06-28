<?php

/**
 * Composer
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.2
 */

namespace BMVC\Libs;

class Composer
{
	
	public static function folderDelete()
	{
		Dir::rm_dir(".git");
		Dir::rm_dir(".github");
	}
}