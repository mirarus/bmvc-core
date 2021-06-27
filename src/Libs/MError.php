<?php

/**
 * MError
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 2.9
 */

namespace BMVC\Libs;

class MError
{

	/**
	 * @var object
	 */
	private static $getInstance;

	/**
	 * @var boolean
	 */
	protected static $html=false;

	/**
	 * @var string
	 */
	protected static $title;

	/**
	 * @var boolean
	 */
	protected static $stop=false;

	/**
	 * @var string
	 */
	protected static $color;

	/**
	 * @var array
	 */
	protected static $colors = [
		'danger' => '244 67 54',
		'warning' => '255 235 59',
		'info' => '3 169 244',
		'success' => '76 175 80',
		'primary' => '0 40 255',
		'dark' => '0 40 60'
	];

	/**
	 * @var array
	 */
	private static $border = [
		'top' => '60',
		'left' => '80'
	];

	public function __construct()
	{
		self::reset();
	}

	/**
	 * @return MErrorA
	 */
	public static function getInstance(): MErrorA
	{
		if (!self::$getInstance) {
			self::$getInstance = new MErrorA;
		}
		return self::$getInstance;
	}

	private static function reset(): void
	{
		self::$html = false;
		self::$title = "System Error!";
		self::$color = self::$colors['primary'];
	}

	/**
	 * @param mixed        $text
	 * @param mixed        $message
	 * @param bool|boolean $html
	 * @param string|null  $title
	 * @param string|null  $color
	 * @param bool|boolean $stop
	 * @param int|integer  $response_code
	 */
	private static function template($text, $message, bool $html=false, string $title=null, string $color=null, bool $stop=false, int $response_code=200): void
	{
		http_response_code($response_code);
		echo $html == true ? '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /><title>' . $title . '</title></head><body>' : null;
		echo '<div style="padding: 15px; border-left: 5px solid rgb(' . $color . ' / ' . self::$border['left'] . '%); border-top: 5px solid rgb(' . $color . ' / ' . self::$border['top'] . '%); background: #f8f8f8; margin-bottom: 10px; border-radius: 5px 5px 0 3px;">';
		echo isset($text) && !empty($text) ? '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; font-size: 16px; font-weight: 500; color: black;">' . $text . "</div>" : null;
		echo isset($message) && !empty($message) ? '<div style="margin-top: 15px; font-size: 14px; font-family: Consolas, Monaco, Menlo, Lucida Console, Liberation Mono, DejaVu Sans Mono, Bitstream Vera Sans Mono, Courier New, monospace, sans-serif; color: #ac0e10;">' . $message . "</div>" : null; 
		echo "</div>";
		echo $html == true ? "</body></html>\n" : "\n";
		if ($stop == true) exit();
	}

	/**
	 * @param mixed        $text
	 * @param mixed        $message
	 * @param bool|boolean $html
	 * @param string|null  $title
	 * @param string|null  $color
	 * @param bool|boolean $stop
	 * @param int|integer  $response_code
	 */
	public static function print($text, $message=null, bool $html=false, string $title=null, string $color=null, bool $stop=false, int $response_code=200): void
	{
		if (self::$color == null) {
			self::$color = self::$colors['primary'];
		}

		if ($color == null) {
			$color = self::$color;
		} else {
			$color = isset(self::$colors[$color]) ? self::$colors[$color] : self::$colors['primary'];
		}

		if ((self::$html == true ? self::$html : $html) == true) {
			$title = isset($title) ? $title : self::$title;
		}

		$stop = isset(self::$stop) ? self::$stop : $stop;
		self::template($text, $message, $html, $title, $color, $stop, $response_code);
		self::reset();
	}

	/**
	 * @param  array $array
	 * @return MError
	 */
	public static function set(array $array): MError
	{
		return self::getInstance()->setData($array);
	}

	/**
	 * @param  string $color
	 * @return MError
	 */
	public static function color(string $color): MError
	{
		return self::getInstance()->setColor($color);
	}

	/**
	 * @param  bool|boolean $bool
	 * @return MError
	 */
	public static function html(bool $bool=false): MError
	{
		return self::getInstance()->setHtml($bool);
	}

	/**
	 * @param  string $title
	 * @return MError
	 */
	public static function title(string $title): MError
	{
		return self::getInstance()->setTitle($title);
	}

	/**
	 * @param  bool|boolean $stop
	 * @return MError
	 */
	public static function stop(bool $stop=true): MError
	{
		return self::getInstance()->setStop($stop);
	}
}

class MErrorA extends MError
{

	/**
	 * @param array $array
	 * @return MErrorA
	 */
	public function setData(array $array): MErrorA
	{
		array_map(function ($key, $value) {
			if ($key == 'color') {
				return $this->setColor($value);
			} if ($key == 'html') {
				return $this->setHtml($value);
			} if ($key == 'title') {
				return $this->setTitle($value);
			} if ($key == 'stop') {
				return $this->setStop($value);
			}
		}, array_keys($array), array_values($array));
		return $this;
	}

	/**
	 * @param string $color
	 * @return MErrorA
	 */
	public function setColor(string $color): MErrorA
	{
		self::$color = self::$colors[$color] ? self::$colors[$color] : self::$colors['info'];
		return $this;
	}

	/**
	 * @param bool|boolean $bool
	 * @return MErrorA
	 */
	public function setHtml(bool $bool=false): MErrorA
	{
		self::$html = $bool;
		return $this;
	}

	/**
	 * @param string $title
	 * @return MErrorA
	 */
	public function setTitle(string $title): MErrorA
	{
		self::$title = $title;
		return $this;
	}

	/**
	 * @param bool|boolean $stop
	 * @return MErrorA
	 */
	public function setStop(bool $stop=true): MErrorA
	{
		self::$stop = $stop;
		return $this;
	}
}