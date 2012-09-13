<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ConstraintTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':name' => NULL,
	);

	public function provider_name()
	{
		return array(
			array(NULL, NULL, ''),

			array('a', new Identifier('a'), 'CONSTRAINT :name '),
			array(new Identifier('b'), new Identifier('b'), 'CONSTRAINT :name '),
			array(new Expression('c'), new Expression('c'), 'CONSTRAINT :name '),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $argument   Argument
	 * @param   mixed   $name       Expected
	 * @param   string  $value
	 */
	public function test_name($argument, $name, $value)
	{
		$constraint = $this->getMockForAbstractClass('SQL\DDL\Constraint', array(''));

		$this->assertSame($constraint, $constraint->name($argument));
		$this->assertEquals($name, $constraint->name);

		$this->assertSame($value, (string) $constraint);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$constraint->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint::__toString
	 */
	public function test_toString()
	{
		$constraint = $this->getMockForAbstractClass('SQL\DDL\Constraint', array(''));

		$this->assertSame('', (string) $constraint);

		$constraint->name('a');

		$this->assertSame('CONSTRAINT :name ', (string) $constraint);
	}
}
