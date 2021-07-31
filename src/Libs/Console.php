<?php

/**
 * Console
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.1
 */

namespace BMVC\Libs;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends SymfonyApplication
{

	public function __construct($dir=null)
	{
		parent::__construct('BMVC', '@Beta');
		$this->setAutoExit(false);

		$this->add(new CommandServerStart($dir));
	//	$this->add(new CommandServerStop());
		$this->add(new CommandMakeController());
		$this->add(new CommandMakeModel());
		$this->add(new CommandClearLog());

		$this->run();
	}
}

class CommandServerStart extends SymfonyCommand
{

	private $dir;

	public function __construct($dir=null)
	{
		parent::__construct();
		$this->dir = $dir;
	}

	protected function configure()
	{
		$this
		->setName('server:start')
		->setDescription('Server Start')
		->setHelp('This command allows you to Server Start...');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$host = "127.0.0.1:8686";
		$url = "http://$host";

		//$this->_kill("php");

		$this->_exec("php -S $host " . ($this->dir ? $this->dir : "../Run.php"));
		$this->_exec("start $url");

		$output->writeln([
			'',
			'Server baslatildi.',
			'------------------------------',
			$host
		]);

		return static::SUCCESS;
	}

	private function _exec($cmd)
	{
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B " . $cmd, "r")); 
		} else {
			exec($cmd . " > /dev/null &");  
		}
	}

	private function _kill($cmd)
	{
		exec("taskkill /F /T /IM $cmd.exe > /dev/null &");
	}
}

class CommandServerStop extends SymfonyCommand
{

	protected function configure()
	{
		$this
		->setName('server:stop')
		->setDescription('Server Stop')
		->setHelp('This command allows you to Server Stop...');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		pr($this->_kill("php"));

		$output->writeln([
			'',
			'Server durduruldu.',
			'------------------------------'
		]);

		return static::SUCCESS;
	}

	private function _kill($cmd)
	{
		exec("taskkill /F /T /IM $cmd.exe > /dev/null &");
	}
}

class CommandMakeController extends SymfonyCommand
{

	protected function configure()
	{
		$this
		->setName('make:controller')
		->setDescription('Controller Create')
		->setHelp('This command allows you to create a controller...')
		->addArgument('class', InputArgument::REQUIRED, 'Class is required');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$data = CmakeCM('controller', $input->getArgument('class'));

		if ($data['status']) {
			$output->writeln([
				'',
				'create a controller.',
				'------------------------------',
				$data['file']
			]);
		} else {
			$output->writeln([
				'',
				'create a controller error.',
				'------------------------------',
				$data['file']
			]);
		}

		return static::SUCCESS;
	}
}

class CommandMakeModel extends SymfonyCommand
{

	protected function configure()
	{
		$this
		->setName('make:model')
		->setDescription('Model Create')
		->setHelp('This command allows you to create a model...')
		->addArgument('class', InputArgument::REQUIRED, 'Class is required');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$data = CmakeCM('model', $input->getArgument('class'));

		if ($data['status']) {
			$output->writeln([
				'',
				'create a model.',
				'------------------------------',
				$data['file']
			]);
		} else {
			$output->writeln([
				'',
				'create a model error.',
				'------------------------------',
				$data['file']
			]);
		}

		return static::SUCCESS;
	}
}

class CommandClearLog extends SymfonyCommand
{

	protected function configure()
	{
		$this
		->setName('clear:log')
		->setDescription('Logs Delete')
		->setHelp('This command allows you to delete a log...');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		array_map('unlink', glob(File::app("Logs/*")));

		$output->writeln([
			'',
			'Logs dizini bosaltildi.',
			'------------------------------'
		]);

		return static::SUCCESS;
	}
}

function CmakeCM(string $type, string $class): array
{
	$useLib = ucfirst($type);

	$class = File::replace($class);
	$file = $class . '.php';

	$parts = File::explode($class);
	$class = array_pop($parts);
	$ns    = File::implode($parts);

	if (file_exists($file)) {

		return [
			'status' => false,
			'file' => $file
		];
	} else {

		$ns ? File::mk_dir($ns) : null;
		$namespace = $ns ? "namespace $ns;\n\n" : null;

		$f = fopen($file, 'w');
		$content = "<?php\n\n{$namespace}use BMVC\\Core\\{$useLib};\n\nclass $class\n{\n\n\tpublic function index()\n\t{\n\t\t\n\t}\n}";
		fwrite($f, $content);
		fclose($f);

		return [
			'status' => true,
			'file' => $file
		];
	}
}

function PsExecute($command, $timeout = 60, $sleep = 2) {

	$pid = PsExec($command);

	if( $pid === false )
		return false;

	$cur = 0;
	while( $cur < $timeout ) {
		sleep($sleep);
		$cur += $sleep;

		echo "\n ---- $cur ------ \n";

		if( !PsExists($pid) )
			return true;
	}
	PsKill($pid);
	return false;
}

function PsExec($commandJob) {
	$command = $commandJob.' > /dev/null 2>&1 & echo $!';
	exec($command, $op);
	$pid = (int)$op[0];
	if($pid!="") return $pid;
	return false;
}

function PsExists($pid) {
	exec("ps ax | grep $pid 2>&1", $output);
	while( list(,$row) = each($output) ) {
		$row_array = explode(" ", $row);
		$check_pid = $row_array[0];

		if($pid == $check_pid) {
			return true;
		}
	}
	return false;
}

function PsKill($pid) {
	exec("kill -9 $pid", $output);
}