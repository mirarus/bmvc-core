<?php

/**
 * Composer
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.1
 */

namespace BMVC\Libs;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Composer
{
	
	/**
	 * @param Event $event
	 */
	public static function postInstall(Event $event)
	{
		Dir::rm_dir(".git");
		Dir::rm_dir(".github");
	}

	/**
	 * @param Event $event
	 */
	public static function postUpdate(Event $event)
	{
		Dir::rm_dir(".git");
		Dir::rm_dir(".github");
	}

	/**
	 * @param PackageEvent $event
	 */
	public static function postPackageInstall(PackageEvent $event)
	{
		Dir::rm_dir(".git");
		Dir::rm_dir(".github");
	}

	/**
	 * @param PackageEvent $event
	 */
	public static function postPackageUpdate(PackageEvent $event)
	{
		Dir::rm_dir(".git");
		Dir::rm_dir(".github");
	}
}