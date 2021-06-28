<?php

use PHPUnit\Framework\TestCase;
use BMVC\Libs\Hash;

class HashTest extends TestCase
{

	public function test_make(): void
	{
		$string = "phpunit/test";

		$this->assertIsString(Hash::make($string));
	}

	public function test_check(): void
	{
		$string = "phpunit/test";

		$this->assertIsBool(Hash::check($string, $string));
	}

	public function test_rehash(): void
	{
		$string = "phpunit/test";

		$this->assertIsBool(Hash::rehash($string));
	}
}