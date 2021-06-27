<?php

/**
 * Log
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 2.3
 */

namespace BMVC\Libs;

use Monolog\Formatter\LineFormatter as MlLineFormatter;
use Monolog\Handler\StreamHandler as MlStreamHandler;
use Monolog\Logger as MlLogger;
use Exception;

class Log
{

	/**
	 * @var string
	 */
	private static $dir = 'Logs';

	public static $monolog;

	public static function monolog(): void
	{
		#
		$formatter = new MlLineFormatter(MlLineFormatter::SIMPLE_FORMAT, MlLineFormatter::SIMPLE_DATE);
		$formatter->includeStacktraces(true);
		#
		$l_file = Dir::implode([Dir::app(self::$dir), 'app.log']);
		$stream = new MlStreamHandler($l_file);
		$stream->setFormatter($formatter);
		#
		$log = new MlLogger('BMVC');
		$log->pushHandler($stream);

		self::$monolog = $log;
	}

	/**
	 * @param mixed $msg
	 */
	public static function emergency($msg): void
	{
		self::write('EMERGENCY', $msg);
	}

	/**
	 * @param mixed $msg
	 */
	public static function alert($msg): void
	{
		self::write('ALERT', $msg);
	}

	/**
	 * @param mixed $msg
	 */
	public static function critical($msg): void
	{
		self::write('CRITICAL', $msg);
	}

	/**
	 * @param mixed $msg
	 */
	public static function error($msg): void
	{
		self::write('ERROR', $msg);
	}

	/**
	 * @param mixed $msg
	 */
	public static function warning($msg): void
	{
		self::write('WARNING', $msg);
	}

	/**
	 * @param mixed $msg
	 */
	public static function notice($msg): void
	{
		self::write('NOTICE', $msg);
	}

	/**
	 * @param mixed $msg
	 */
	public static function info($msg): void
	{
		self::write('INFO', $msg);
	}

	/**
	 * @param mixed $msg
	 */
	public static function debug($msg): void
	{
		self::write('DEBUG', $msg);
	}

	/**
	 * @param string $level
	 * @param mixed $msg
	 */
	private static function write(string $level, $msg): void
	{
		if (is_array($msg)) {
			$msg = @implode(', ', $msg);
		}
		self::save('[' . date('Y-m-d\TH:i:sP') . '] ' . $level . '.' . Request::getRequestMethod() . ': ' . $msg);
	}

	/**
	 * @param string $text
	 */
	private static function save(string $text): void
	{
		$dir = Dir::app(self::$dir);
		Dir::mk_dir($dir);
		$file = Dir::implode([$dir, 'bmvc.log']);

		$file = fopen($file, 'a');
		if (fwrite($file, $text . "\r\n") === false) {
			throw new Exception('Log Error! | Failed to create log file. - Check the write permissions.');
		}
		fclose($file);
	}
}