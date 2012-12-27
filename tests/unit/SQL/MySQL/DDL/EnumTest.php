<?php
namespace SQL\MySQL\DDL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class EnumTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'ENUM ()'),
			array(array('a'), 'ENUM (?)'),
			array(array('a', 'b'), 'ENUM (?, ?)'),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Enum::__construct
	 * @covers  SQL\MySQL\DDL\Enum::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $value
	 */
	public function test_constructor($argument, $value)
	{
		$enum = new Enum($argument);

		$this->assertSame($argument, $enum->values);

		$this->assertSame($value, (string) $enum);
		$this->assertSame($argument, $enum->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Enum::__construct
	 */
	public function test_constructor_default()
	{
		$enum = new Enum;

		$this->assertSame(array(), $enum->values);

		$this->assertSame('ENUM ()', (string) $enum);
		$this->assertSame(array(), $enum->parameters);
	}

}
