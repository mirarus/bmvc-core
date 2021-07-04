<?php

/**
 * Response
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.6
 */

namespace BMVC\Libs;

class Response
{
	
	/**
	 * @var array
	 */
	private static $statusCodes = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	];

	/**
	 * @param int $code
	 */
	public static function setHeader(int $code): void
	{
		@header("HTTP/1.1 " . $code . " " . self::setStatusCode($code));
		@header("Content-Type: application/json; charset=utf-8");
	}
	
	/**
	 * @param int $code
	 */
	public static function setStatusCode(int $code): void
	{
		http_response_code($code);
	}

	/**
	 * @return int
	 */
	public static function getStatusCode(): int
	{
		return http_response_code();
	}

	/**
	 * @param  int|null $code
	 * @return string
	 */
	public static function getStatusMessage(int $code=null): string
	{
		if (is_null($code)) {
			return self::$statusCodes[self::getStatusCode()];
		}
		return self::$statusCodes[$code];
	}

	/**
	 * @param mixed        $data
	 * @param bool|boolean $status
	 * @param int|integer  $code
	 */
	public static function json($data=null, bool $status=true, int $code=200)
	{
		self::setStatusCode($code);
		@header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
		@header('Content-type: application/json');
		//@header('Status: ' . self::$statusCodes[$code]);
		return json_encode(['status' => $status, 'message' => $data]);
	}

	/**
	 * @param array
	 * @param int|integer
	 */
	public static function _json(array $data=[], int $code=200)
	{
		self::setStatusCode($code);
		@header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
		@header('Content-type: application/json');
		return json_encode($data);
	}
}