<?php
namespace SQL\DDL\Constraint;

use SQL\Column;
use SQL\Expression;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class PrimaryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':name' => NULL,
		':columns' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, 'PRIMARY KEY'),
			array(
				array(array('a')),
				array(new Column('a')), 'PRIMARY KEY (:columns)',
			),
			array(
				array(array('a', 'b')),
				array(new Column('a'), new Column('b')),
				'PRIMARY KEY (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Primary::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $columns    Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $columns, $value)
	{
		$class = new \ReflectionClass('SQL\DDL\Constraint\Primary');
		$primary = $class->newInstanceArgs($arguments);

		$this->assertEquals($columns, $primary->columns);

		$this->assertSame($value, (string) $primary);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$primary->parameters
		);
	}

	public function provider_columns()
	{
		return array(
			array(NULL, NULL, 'PRIMARY KEY'),

			array(
				array('a'),
				array(new Column('a')), 'PRIMARY KEY (:columns)',
			),
			array(
				array('a', 'b'),
				array(new Column('a'), new Column('b')),
				'PRIMARY KEY (:columns)',
			),

			array(
				array(new Column('a')),
				array(new Column('a')), 'PRIMARY KEY (:columns)',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'PRIMARY KEY (:columns)',
			),

			array(
				array(new Expression('a')),
				array(new Expression('a')), 'PRIMARY KEY (:columns)',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'PRIMARY KEY (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Primary::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $columns    Expected
	 * @param   string  $value
	 */
	public function test_columns($argument, $columns, $value)
	{
		$primary = new Primary;

		$this->assertSame($primary, $primary->columns($argument));
		$this->assertEquals($columns, $primary->columns);

		$this->assertSame($value, (string) $primary);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$primary->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Primary::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$primary = new Primary;
		$primary->columns($argument);

		$this->assertSame($primary, $primary->columns(NULL));
		$this->assertNull($primary->columns);

		$this->assertSame('PRIMARY KEY', (string) $primary);
		$this->assertSame($this->parameters, $primary->parameters);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Primary::__toString
	 */
	public function test_toString()
	{
		$primary = new Primary;
		$primary
			->name('a')
			->columns(array('b'));

		$this->assertSame(
			'CONSTRAINT :name PRIMARY KEY (:columns)', (string) $primary
		);
	}
}
