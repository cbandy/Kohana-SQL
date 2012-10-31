<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function provider_string_code()
	{
		return array(
			array('rat'),
			// #3358
			array('3F000'),
			// #3404
			array('42S22'),
			// #4039
			array('25P01'),
		);
	}

	/**
	 * @covers  SQL\RuntimeException::__construct
	 *
	 * @dataProvider    provider_string_code
	 *
	 * @param   string  $code
	 */
	public function test_string_code($code)
	{
		$exception = new RuntimeException('', $code);
		$this->assertSame($code, $exception->getCode());
	}
}
