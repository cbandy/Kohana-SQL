<?php
namespace SQL\DDL\Constraint;

use SQL\Column;
use SQL\Expression;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class UniqueTest extends \PHPUnit_Framework_TestCase
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
			array(array(), NULL, 'UNIQUE'),
			array(
				array(array('a')),
				array(new Column('a')), 'UNIQUE (:columns)',
			),
			array(
				array(array('a', 'b')),
				array(new Column('a'), new Column('b')), 'UNIQUE (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Unique::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $columns    Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $columns, $value)
	{
		$class = new \ReflectionClass('SQL\DDL\Constraint\Unique');
		$unique = $class->newInstanceArgs($arguments);

		$this->assertEquals($columns, $unique->columns);

		$this->assertSame($value, (string) $unique);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$unique->parameters
		);
	}

	public function provider_columns()
	{
		return array(
			array(NULL, NULL, 'UNIQUE'),

			array(
				array('a'),
				array(new Column('a')), 'UNIQUE (:columns)',
			),
			array(
				array('a', 'b'),
				array(new Column('a'), new Column('b')), 'UNIQUE (:columns)',
			),

			array(
				array(new Column('a')),
				array(new Column('a')), 'UNIQUE (:columns)',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')), 'UNIQUE (:columns)',
			),

			array(
				array(new Expression('a')),
				array(new Expression('a')), 'UNIQUE (:columns)',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'UNIQUE (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Unique::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $columns    Expected
	 * @param   string  $value
	 */
	public function test_columns($argument, $columns, $value)
	{
		$unique = new Unique;

		$this->assertSame($unique, $unique->columns($argument));
		$this->assertEquals($columns, $unique->columns);

		$this->assertSame($value, (string) $unique);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$unique->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Unique::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$unique = new Unique;
		$unique->columns($argument);

		$this->assertSame($unique, $unique->columns(NULL));
		$this->assertNull($unique->columns);

		$this->assertSame('UNIQUE', (string) $unique);
		$this->assertSame($this->parameters, $unique->parameters);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Unique::__toString
	 */
	public function test_toString()
	{
		$unique = new Unique;
		$unique
			->name('a')
			->columns(array('b'));

		$this->assertSame(
			'CONSTRAINT :name UNIQUE (:columns)', (string) $unique
		);
	}
}
