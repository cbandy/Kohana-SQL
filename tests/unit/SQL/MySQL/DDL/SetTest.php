<?php
namespace SQL\MySQL\DDL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class SetTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'SET ()'),
			array(array('a'), 'SET (?)'),
			array(array('a', 'b'), 'SET (?, ?)'),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Set::__construct
	 * @covers  SQL\MySQL\DDL\Set::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $value
	 */
	public function test_constructor($argument, $value)
	{
		$enum = new Set($argument);

		$this->assertSame($argument, $enum->values);

		$this->assertSame($value, (string) $enum);
		$this->assertSame($argument, $enum->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Set::__construct
	 */
	public function test_constructor_default()
	{
		$enum = new Set;

		$this->assertSame(array(), $enum->values);

		$this->assertSame('SET ()', (string) $enum);
		$this->assertSame(array(), $enum->parameters);
	}

}
