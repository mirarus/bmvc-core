<?php

use PHPUnit\Framework\TestCase;
use BMVC\Libs\CL;

class CLTest extends TestCase
{

	public function test_replace(): void
	{
		$string = "phpunit/test";

		$this->assertIsString(CL::replace($string));
	}

	public function test_implode(): void
	{
		$array = ['test', 'phpunit'];

		$this->assertIsString(CL::implode($array));
	}

	public function test_explode(): void
	{
		$string = "phpunit/test";

		$this->assertIsArray(CL::explode($string));
	}

	public function test_trim(): void
	{
		$string = "phpunit/test";

		$this->assertIsString(CL::trim($string));
	}
}