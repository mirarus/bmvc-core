<?php

/**
 * Console
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.0
 */

namespace BMVC\Libs;

class Console
{

	/**
	 * @var array
	 */
	private static $params=[];

	/**
	 * @param array $args
	 */
	public function __construct(array $args=[])
	{
		self::Run($args);
	}

	/**
	 * @param array $args
	 */
	public static function Run(array $args=[]): string
	{
		self::$params = $params = array_slice($args, 1);

		if (!self::$params) {
			return self::help();
		} else {
			if ($params[0] == '-h') {
				return self::help();
			} else if ($params[0] == '-v') {
				return self::version();
			} else if (strpos($params[0], ':') !== false) {
				$slice = explode(':', $params[0]);
				if ($slice[0] == 'make') {
					if (array_key_exists(1, $params)) {
						return self::make($slice[1], $params[1]);
					} else {
						return self::make($slice[1]);
					}
				} else if ($slice[0] == 'clear') {
					return self::clear($slice[1]);
				} else if ($slice[0] == 'server') {
					return self::server($slice[1]);
				} else {
					return self::string('Gecersiz komut. "' . $params[0] . '"');
				}
			} else {
				return self::string('Gecersiz komut. "' . $params[0] . '"');
			}
		}
	}

	/**
	 * @return string
	 */
	private static function help(): string
	{
		$line = str_pad("-", 60, "-");
		return self::string(" - Make\n\n[make:controller] (make:controller NameSpace\MyController)\n\tController olusturmak icin kullanilir.\n\n[make:model] (make:model NameSpace\MyModel)\n\tModel olusturmak icin kullanilir.\n\n$line\n\n - Clear\n\n[clear:logs]\n\tLogs dizinini temizlemek icin kullanilir.\n\n[-v]\n\tBMVC versiyon bilgisini verir.\n\n[-h]\n\tTum console komutlari ile ilgili bilgi verir.");
	}

	/**
	 * @param  string      $command
	 * @param  string|null $params
	 * @return string
	 */
	private static function make(string $command, string $params=null): string
	{
		switch ($command) {
			case 'controller' : return self::makeController($params); break;
			case 'model'      : return self::makeModel($params); break;
			default           : return self::string('"make" komutu icin gecersiz komut. ' . $command);
		}
	}

	/**
	 * @param  string|null $controller
	 * @return string
	 */
	private static function makeController(string $controller=null): string
	{
		if ($controller) {
			$controller = str_replace(['//', '/', '\\'], '/', $controller);
			$file = $controller . '.php';

			$parts = explode('/', $controller);
			$class = array_pop($parts);
			$ns    = implode('\\', $parts);

			if (file_exists($file)) {
				return self::string("Controller zaten mevcut: $file");
			} else {

				$ns ? self::makeDir($ns) : null;
				$namespace = $ns ? "namespace $ns;\n\n" : null;

				$f = fopen($file, 'w');
				$content = "<?php\n\n{$namespace}use BMVC\\Core\\Controller;\n\nclass $class\n{\n\n\tpublic function index()\n\t{\n\t\t\n\t}\n}";
				fwrite($f, $content);
				fclose($f);

				return self::string("Controller olusturuldu: $file");
			}
		} else {
			return self::string('"make:controller" komutu icin eksik parametre.');
		}
	}

	/**
	 * @param  string|null $model
	 * @return string
	 */
	private static function makeModel(string $model=null): string
	{
		if ($model) {
			$model = str_replace(['//', '/', '\\'], '/', $model);
			$file = $model . '.php';

			$parts = explode('/', $model);
			$class = array_pop($parts);
			$ns    = implode('\\', $parts);

			if (file_exists($file)) {
				return self::string("Model zaten mevcut: $file");
			} else {

				$ns ? self::makeDir($ns) : null;
				$namespace = $ns ? "namespace $ns;\n\n" : null;

				$f = fopen($file, 'w');
				$content = "<?php\n\n{$namespace}use BMVC\\Core\\Model;\n\nclass $class\n{\n\n\tpublic function index()\n\t{\n\t\t\n\t}\n}";
				fwrite($f, $content);
				fclose($f);

				return self::string("Model olusturuldu: $file");
			}
		} else {
			return self::string('"make:model" komutu icin eksik parametre.');
		}
	}

	/**
	 * @param  string $command
	 * @return string
	 */
	private static function clear(string $command): string
	{
		switch ($command) {
			case 'logs' : return self::clearLogs(); break;
			default		  : return self::string('"clear" komutu icin gecersiz parametre. ' . $command);
		}
	}

	/**
	 * @return string
	 */
	private static function clearLogs(): string
	{
		array_map('unlink', glob("Logs/*"));
		return self::string('Logs dizini bosaltildi.');
	}

	/**
	 * @param  string $command
	 * @return string
	 */
	private static function server(string $command): string
	{
		switch ($command) {
			case 'start' : return self::serverStart(); break;
			default		  : return self::string('"server" komutu icin gecersiz parametre. ' . $command);
		}
	}

	/**
	 * @return string
	 */
	private static function serverStart(): string
	{
	pr(self::_exec('php -S 127.0.0.1:8686 .'));

		return self::string('Server baslatildi.');
	}

	/**
	 * @return string
	 */
	private static function version(): string
	{
		return self::string("Version beta");
	}

	/**
	 * @param  string|null $string
	 * @return string
	 */
	private static function string(string $string=null): string
	{
		$line = str_pad("-", 60, "-");
		$space = str_pad(" ", 25, " ");

		return 
		"$line\n| -{$space}BMVC{$space}- |\n$line\n|
		\n$string
		\n|\n$line
		";
	}

	/**
	 * @param  string      $path
	 * @param  int|integer $permissions
	 * @return string
	 */
	private static function makeDir(string $path, int $permissions=0755): string
	{
		return is_dir($path) || mkdir($path, $permissions, true);
	}

	/**
	 * @param mixed $cmd
	 */
	private static function _exec($cmd)
	{
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B " . $cmd, "r")); 
		} else {
			exec($cmd . " > /dev/null &");  
		}
	}
}