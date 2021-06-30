<?php

/**
 * JWT
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.1
 */

namespace BMVC\Libs;

use Exception;
use DateTime;

class Jwt
{

	/**
	 * @var integer
	 */
	private static $exp = 300;
	
	/**
	 * @var integer
	 */
	private static $leeway = 0;

	/**
	 * @var array
	 */
	private static $algorithms = [
		'HS256' => 'SHA256',
		'HS512' => 'SHA512',
		'HS384' => 'SHA384'
	];

	public static function encode($payload, $secret, $alg='HS256', $head=null)
	{
		$header = [
			'typ' => 'JWT',
			'alg' => $alg
		];
		if ($head !== null && is_array($head)) {
			array_merge($head, $header);
		}
		$payload['jwt']['exp'] = time() + self::$exp;
		$payload['jwt']['jti'] = uniqid(time());
		$payload['jwt']['iat'] = time();
		$header         = self::urlSafeBase64Encode(self::jsonEncode($header));
		$payload        = self::urlSafeBase64Encode(self::jsonEncode($payload));
		$message        = $header . '.' . $payload;
		$signature      = self::urlSafeBase64Encode(self::signature($message, $secret, $alg));
		return $header . '.' . $payload . '.' . $signature;
	}

	public static function decode($token, $secret)
	{
		if (empty($secret)) {
			throw new Exception('JWT Error! | Secret may not be empty.');
		}
		$jwt = explode('.', $token);
		if (count($jwt) != 3) {
			throw new Exception('JWT Error! | Wrong number of segments.');
		}
		list ($head64, $payload64, $sign64) = $jwt;
		if (null === ($header = self::jsonDecode(self::urlSafeBase64Decode($head64)))) {
			throw new Exception('JWT Error! | Invalid header encoding.');
		}
		if (null === ($payload = self::jsonDecode(self::urlSafeBase64Decode($payload64)))) {
			throw new Exception('JWT Error! | Invalid claims encoding.');
		}
		if (false === ($signature = self::urlSafeBase64Decode($sign64))) {
			throw new Exception('JWT Error! | Invalid signature encoding.');
		}
		if (empty($header->alg)) {
			throw new Exception('JWT Error! | Empty algorithm.');
		}
		if (empty(self::$algorithms[$header->alg])) {
			throw new Exception('JWT Error! | Algorithm not supported.');
		}
		if (!self::verify("$head64.$payload64", $signature, $secret, $header->alg)) {
			throw new Exception('JWT Error! | Signature verification failed.');
		}
		if (isset($payload->jwt->iat) && $payload->jwt->iat > (time() + self::$leeway)) {
			throw new Exception('JWT Error! | Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->jwt->iat));
		}
		if (isset($payload->jwt->exp) && (time() - self::$leeway) >= $payload->jwt->exp) {
			throw new Exception('JWT Error! | Expired token.');
		}
		return $payload;
	}

	private static function signature($message, $secret, $alg)
	{
		if (!array_key_exists($alg, self::$algorithms)) {
			throw new Exception('JWT Error! | Algorithm not supported.');
		}
		return hash_hmac(self::$algorithms[$alg], $message, $secret, true);
	}

	private static function verify($message, $signature, $secret, $alg)
	{
		if (empty(self::$algorithms[$alg])) {
			throw new Exception('JWT Error! | Algorithm not supported.');
		}
		$hash   = hash_hmac(self::$algorithms[$alg], $message, $secret, true);
		if (function_exists('hash_equals')) {
			return hash_equals($signature, $hash);
		}
		$len = min(self::safeStrLen($signature), self::safeStrLen($hash));
		$status = 0;
		for ($i = 0; $i < $len; $i++) {
			$status |= (ord($signature[$i]) ^ ord($hash[$i]));
		}
		$status |= (self::safeStrLen($signature) ^ self::safeStrLen($hash));
		return ($status === 0);
	}

	private static function urlSafeBase64Encode($data)
	{
		return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
	}

	private static function urlSafeBase64Decode($data)
	{
		$remainder  = strlen($data) % 4;
		if ($remainder) {
			$padlen = 4 - $remainder;
			$data  .= str_repeat('=', $padlen);
		}
		return base64_decode(strtr($data, '-_', '+/'));
	}

	private static function jsonEncode($data)
	{
		$json = json_encode($data);
		if (function_exists('json_last_error') && $errno = json_last_error()) {
			self::handleJsonError($errno);
		} elseif ($json === 'null' && $data !== null) {
			throw new Exception('JWT Error! | Null result with non-null input.');
		}
		return $json;
	}

	private static function jsonDecode($data)
	{
		$obj = json_decode($data, false, 512, JSON_BIGINT_AS_STRING);
		
		if (function_exists('json_last_error') && $errno = json_last_error()) {
			self::handleJsonError($errno);
		} elseif ($obj === null && $data !== 'null') {
			throw new Exception('JWT Error! | Null result with non-null input.');
		}
		return $obj;
	}

	private static function handleJsonError($errno)
	{
		$messages = [
			JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
			JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
			JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
			JSON_ERROR_UTF8           => 'Malformed UTF-8 characters'
		];
		throw new Exception('JWT Error! | ' . isset($messages[$errno]) ? $messages[$errno] : 'Unknown JSON error: ' . $errno);
	}

	private static function safeStrLen($str)
	{
		if (function_exists('mb_strlen')) {
			return mb_strlen($str, '8bit');
		}
		return strlen($str);
	}
}