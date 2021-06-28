<?php

use PHPUnit\Framework\TestCase;
use BMVC\Libs\Dir;

class DirTest extends TestCase
{

	public function test_base(): void
	{
		$this->assertIsString(Dir::base());
	}

	public function test_app(): void
	{
		$this->assertIsString(Dir::app());
	}

	public function test_get(): void
	{
		$this->assertIsArray(Dir::get());
	}

	public function test_is_dir(): void
	{
		$string = "phpunit/test";

		$this->assertIsBool(Dir::is_dir($string));
	}

	public function test_replace(): void
	{
		$string = "phpunit/test";

		$this->assertIsString(Dir::replace($string));
	}

	public function test_implode(): void
	{
		$array = ['test', 'phpunit'];

		$this->assertIsString(Dir::implode($array));
	}

	public function test_explode(): void
	{
		$string = "phpunit/test";

		$this->assertIsArray(Dir::explode($string));
	}

	public function test_trim(): void
	{
		$string = "phpunit/test";

		$this->assertIsString(Dir::trim($string));
	}

	public function test_mk_dir(): void
	{
		$string = "phpunit";

		$this->assertIsBool(Dir::mk_dir($string));
	}

	public function test_rm_dir(): void
	{
		$string = "phpunit";

		$this->assertIsBool(Dir::rm_dir($string));
	}
}