<?php

/**
 * Csrf
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.9
 */

namespace BMVC\Libs;

use stdClass;

class Csrf
{

	/**
	 * @var string
	 */
	private static $page = "b4e27faacd7a7d7ed04aecb30bd29451";

	/**
	 * @param  int|integer $expiry
	 * @return mixed
	 */
	public static function token(int $expiry=3600)
	{
		return self::getToken(null, $expiry);
	}

	/**
	 * @param  int|integer $expiry
	 * @return mixed
	 */
	public static function input(int $expiry=3600)
	{
		$token = self::getToken(null, $expiry);
		if ($token) {
			return '<input type="hidden" name="csrf_token" value="'. $token .'">' . "\r\n";
		}
	}

	/**
	 * @param  string|null $token
	 * @return mixed
	 */
	public static function verify(string $token=null)
	{
		return self::verifyToken(null, false, $token);
	}

	/**
	 * @param  string|null $page
	 * @param  int|integer $expiry
	 * @return mixed
	 */
	private static function getToken(string $page=null, int $expiry=3600)
	{
		$page = $page ? $page : self::$page;

		self::confirmSessionStarted();

		if (empty($page)) {
			return false;
		}

		$token = (self::getSessionToken($page) ? self::getSessionToken($page) : self::setNewToken($page, $expiry));

		return $token->sessiontoken;
	}

	/**
	 * @param  string|null  $page
	 * @param  bool|boolean $removeToken
	 * @param  string|null  $requestToken
	 * @return bool
	 */
	private static function verifyToken(string $page=null, bool $removeToken=false, string $requestToken=null): bool
	{
		$page = $page ? $page : self::$page;

		self::confirmSessionStarted();

		$requestToken = ($requestToken ? $requestToken : $_POST['csrf_token']);

		if (empty($page)) {
			return false;
		} else if (empty($requestToken)) {
			return false;
		}

		$token = self::getSessionToken($page);

		if (empty($token) || time() > (int) $token->expiry) {
			self::removeToken($page);
			return false;
		}

		$sessionConfirm = hash_equals($token->sessiontoken, $requestToken);
		$cookieConfirm  = hash_equals($token->cookietoken, self::getCookieToken($page));

		if ($removeToken) {
			self::removeToken($page);
		}

		if ($sessionConfirm || $cookieConfirm) {
			return true;
		}
		return false;
	}

	/**
	 * @param  string $page
	 * @return bool
	 */
	private static function removeToken(string $page): bool
	{
		self::confirmSessionStarted();

		if (empty($page)) {
			return false;
		}

		unset($_COOKIE[self::makeCookieName($page)], $_SESSION['csrf_tokens'][$page]);

		return true;
	}

	/**
	 * @param string $page
	 * @param int    $expiry
	 * @return mixed
	 */
	private static function setNewToken(string $page, int $expiry)
	{
		$token = new stdClass();
		$token->page   		 	 = $page;
		$token->expiry 		 	 = time() + $expiry;
		$token->sessiontoken = base64_encode(random_bytes(32));
		$token->cookietoken  = md5(base64_encode(random_bytes(32)));

		@setcookie(self::makeCookieName($page), $token->cookietoken, $token->expiry);

		return $_SESSION['csrf_tokens'][$page] = $token;
	}

	/**
	 * @param  string|null $page
	 * @return mixed
	 */
	private static function getSessionToken(string $page=null)
	{
		return !empty($_SESSION['csrf_tokens'][$page]) ? $_SESSION['csrf_tokens'][$page] : null;
	}

	/**
	 * @param  string $page
	 * @return string
	 */
	private static function getCookieToken(string $page) : string
	{
		$value = self::makeCookieName($page);
		return !empty($_COOKIE[$value]) ? $_COOKIE[$value] : '';
	}

	/**
	 * @param  string $page
	 * @return string
	 */
	private static function makeCookieName(string $page): string
	{
		if (empty($page)) {
			return '';
		}
		return 'csrf_token-' . substr(md5($page), 0, 10);
	}

	/**
	 * @return bool
	 */
	private static function confirmSessionStarted(): bool
	{
		if (!isset($_SESSION)) {
			return false;
		}
		return true;
	}
}