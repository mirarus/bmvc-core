<?php

use PHPUnit\Framework\TestCase;
use BMVC\Libs\Convert;

class ConvertTest extends TestCase
{

	public function test_arr_obj(): void
	{
		$array = [
			'test' => 'phpunit', 
			'phpunit' => 'test'
		];

		$this->assertIsObject(Convert::arr_obj($array));
	}

	public function test_obj_arr(): void
	{
		$object = new stdClass;
		$object->test = "phpunit";
		$object->phpunit = "test";

		$this->assertIsArray(Convert::obj_arr($object));
	}

	public function test_arr_xml(): void
	{
		$array = [
			'test' => 'phpunit', 
			'phpunit' => 'test'
		];

		$this->assertIsString(Convert::arr_xml($array));
	}
}