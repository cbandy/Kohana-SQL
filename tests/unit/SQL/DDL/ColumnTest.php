<?php
namespace SQL\DDL;

use SQL\Column as SQL_Column;
use SQL\Conditions;
use SQL\DDL\Constraint\Check;
use SQL\DDL\Constraint\Unique;
use SQL\Expression;
use SQL\Identifier;
use SQL\Listing;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ColumnTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':constraints' => NULL,
		':default' => NULL,
		':name' => NULL,
		':type' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL),
			array(array('a'), new SQL_Column('a'), NULL),
			array(array('b', 'c'), new SQL_Column('b'), new Expression('c')),
		);
	}

	/**
	 * @covers  SQL\DDL\Column::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array       $arguments  Arguments
	 * @param   SQL\Column  $name       Expected property
	 * @param   Expression  $type       Expected property
	 */
	public function test_constructor($arguments, $name, $type)
	{
		$class = new \ReflectionClass('SQL\DDL\Column');
		$column = $class->newInstanceArgs($arguments);

		$this->assertEquals($name, $column->name);
		$this->assertEquals($type, $column->type);

		$this->assertSame(':name :type', (string) $column);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(':name' => $name, ':type' => $type)
			),
			$column->parameters
		);
	}

	public function provider_name()
	{
		return array(
			array('a', new SQL_Column('a')),
			array(new Identifier('b'), new Identifier('b')),
			array(new Expression('expr'), new Expression('expr')),
		);
	}

	/**
	 * @covers  SQL\DDL\Column::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $argument   Argument
	 * @param   mixed   $name       Expected
	 */
	public function test_name($argument, $name)
	{
		$column = new Column;

		$this->assertSame($column, $column->name($argument));
		$this->assertEquals($name, $column->name);

		$this->assertSame(':name :type', (string) $column);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$column->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Column::no_default
	 *
	 * @dataProvider    provider_set_default
	 *
	 * @param   mixed   $default    Argument
	 */
	public function test_no_default($default)
	{
		$column = new Column;
		$column->default = $default;

		$this->assertSame($column, $column->no_default());
		$this->assertFalse($column->has_default);
		$this->assertNull($column->default);

		$this->assertSame(':name :type', (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	public function provider_not_null()
	{
		return array(
			array(array(), TRUE, ':name :type NOT NULL'),
			array(array(TRUE), TRUE, ':name :type NOT NULL'),
			array(array(FALSE), FALSE, ':name :type'),
		);
	}

	/**
	 * @covers  SQL\DDL\Column::not_null
	 *
	 * @dataProvider    provider_not_null
	 *
	 * @param   array   $arguments  Arguments
	 * @param   boolean $not_null   Expected
	 * @param   string  $value
	 */
	public function test_not_null($arguments, $not_null, $value)
	{
		$column = new Column;

		$this->assertSame(
			$column,
			call_user_func_array(array($column, 'not_null'), $arguments)
		);
		$this->assertSame($not_null, $column->not_null);

		$this->assertSame($value, (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	public function provider_set_default()
	{
		return array(
			array(NULL),
			array(1),
			array('a'),
			array(new Expression('b')),
			array(new Identifier('c')),
		);
	}

	/**
	 * @covers  SQL\DDL\Column::set_default
	 * @covers  SQL\DDL\Column::__get
	 *
	 * @dataProvider    provider_set_default
	 *
	 * @param   mixed   $default    Argument
	 */
	public function test_set_default_method($default)
	{
		$column = new Column;

		$this->assertSame($column, $column->set_default($default));
		$this->assert_default($column, $default);
	}

	/**
	 * @covers  SQL\DDL\Column::__get
	 * @covers  SQL\DDL\Column::__set
	 *
	 * @dataProvider    provider_set_default
	 *
	 * @param   mixed   $default    Argument
	 */
	public function test_set_default_property($default)
	{
		$column = new Column;
		$column->default = $default;

		$this->assert_default($column, $default);
	}

	protected function assert_default($column, $default)
	{
		$this->assertTrue($column->has_default);
		$this->assertSame($default, $column->default);

		$this->assertSame(':name :type DEFAULT :default', (string) $column);
		$this->assertEquals(
			array_merge($this->parameters, array(':default' => $default)),
			$column->parameters
		);
	}

	public function provider_type()
	{
		return array(
			array('a', new Expression('a')),
			array(new Expression('b'), new Expression('b')),
		);
	}

	/**
	 * @covers  SQL\DDL\Column::type
	 *
	 * @dataProvider    provider_type
	 *
	 * @param   mixed       $argument   Argument
	 * @param   Expression  $type       Expected
	 */
	public function test_type($argument, $type)
	{
		$column = new Column;

		$this->assertSame($column, $column->type($argument));
		$this->assertEquals($type, $column->type);

		$this->assertSame(':name :type', (string) $column);
		$this->assertEquals(
			array_merge($this->parameters, array(':type' => $type)),
			$column->parameters
		);
	}

	public function provider_constraints()
	{
		return array(
			array(new Unique, new Unique),
			array(new Expression('expr'), new Expression('expr')),
			array(
				array(new Unique, new Check(new Conditions('a', '=', 'b'))),
				new Listing(
					' ',
					array(new Unique, new Check(new Conditions('a', '=', 'b')))
				),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Column::constraints
	 *
	 * @dataProvider    provider_constraints
	 *
	 * @param   mixed   $argument       Argument
	 * @param   mixed   $constraints    Expected
	 */
	public function test_constraints($argument, $constraints)
	{
		$column = new Column;

		$this->assertSame($column, $column->constraints($argument));
		$this->assertEquals($constraints, $column->constraints);

		$this->assertSame(':name :type :constraints', (string) $column);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(':constraints' => $constraints)
			),
			$column->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Column::constraints
	 *
	 * @dataProvider    provider_constraints
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_constraints_reset($argument)
	{
		$column = new Column;
		$column->constraints($argument);

		$this->assertSame($column, $column->constraints(NULL));
		$this->assertNull($column->constraints);

		$this->assertSame(':name :type', (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	/**
	 * @covers  SQL\DDL\Column::__toString
	 */
	public function test_toString()
	{
		$column = new Column;
		$column
			->name('a')
			->type('b')
			->set_default('c')
			->not_null()
			->constraints(new Unique);

		$this->assertSame(
			':name :type DEFAULT :default NOT NULL :constraints',
			(string) $column
		);
	}
}
