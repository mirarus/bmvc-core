<?php

/**
 * Log
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.9
 */

namespace BMVC\Libs;

use Exception;
use BMVC\Libs\{Request, Dir};

class Log
{

	/**
	 * @param mixed $message
	 */
	public static function emergency($message): void
	{
		self::write('EMERGENCY', $message);
	}

	/**
	 * @param mixed $message
	 */
	public static function alert($message): void
	{
		self::write('ALERT', $message);
	}

	/**
	 * @param mixed $message
	 */
	public static function critical($message): void
	{
		self::write('CRITICAL', $message);
	}

	/**
	 * @param mixed $message
	 */
	public static function error($message): void
	{
		self::write('ERROR', $message);
	}

	/**
	 * @param mixed $message
	 */
	public static function warning($message): void
	{
		self::write('WARNING', $message);
	}

	/**
	 * @param mixed $message
	 */
	public static function notice($message): void
	{
		self::write('NOTICE', $message);
	}

	/**
	 * @param mixed $message
	 */
	public static function info($message): void
	{
		self::write('INFO', $message);
	}

	/**
	 * @param mixed $message
	 */
	public static function debug($message): void
	{
		self::write('DEBUG', $message);
	}

	/**
	 * @param string $level
	 * @param mixed $message
	 */
	private static function write(string $level, $message): void
	{
		if (is_array($message)) {
			$message = @implode(', ', $message);
		}
		self::save('[' . date('Y-m-d\TH:i:sP') . '] ' . $level . '.' . Request::getRequestMethod() . ': ' . $message);
	}

	/**
	 * @param string $text
	 */
	private static function save(string $text): void
	{
		if (!_is_dir(Dir::app('/App/Logs/'))) {
			@mkdir(Dir::app('/App/Logs/'));
		}

		$file = 'bmvc.log';
		$file = fopen(Dir::app('/App/Logs/' . $file), 'a');
		if (fwrite($file, $text . "\r\n") === false) {
			throw new Exception('Log Error! | Failed to create log file. - Check the write permissions.');
		}
		fclose($file);
	}
}
