<?php
namespace SQL\DDL\Constraint;

use SQL\Expression;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class CheckTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':name' => NULL,
		':conditions' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL),
			array(array(1), 1),
			array(array(new Expression('a')), new Expression('a')),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Check::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments
	 * @param   mixed   $conditions Expected
	 */
	public function test_constructor($arguments, $conditions)
	{
		$class = new \ReflectionClass('SQL\DDL\Constraint\Check');
		$check = $class->newInstanceArgs($arguments);

		$this->assertEquals($conditions, $check->conditions);

		$this->assertSame('CHECK (:conditions)', (string) $check);
		$this->assertEquals(
			array_merge($this->parameters, array(':conditions' => $conditions)),
			$check->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Check::conditions
	 */
	public function test_conditions()
	{
		$check = new Check;

		$this->assertSame($check, $check->conditions(1));
		$this->assertSame(1, $check->conditions);

		$this->assertSame('CHECK (:conditions)', (string) $check);
		$this->assertSame(
			array_merge($this->parameters, array(':conditions' => 1)),
			$check->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Check::__toString
	 */
	public function test_toString()
	{
		$check = new Check;
		$check->name('a');

		$this->assertSame('CONSTRAINT :name CHECK (:conditions)', (string) $check);
	}
}
