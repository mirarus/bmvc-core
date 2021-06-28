<?php

use PHPUnit\Framework\TestCase;
use BMVC\Libs\Benchmark;

class BenchmarkTest extends TestCase
{

	public function test_memory(): void
	{
		$this->assertIsString(Benchmark::memory());
	}

	/*public function test_Run(): void
	{
		$this->assertIsString(Benchmark::run());
	}*/
}