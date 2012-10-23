<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Create_TableTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':columns' => NULL,
		':constraints' => NULL,
		':name' => NULL,
		':query' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL),
			array(array('a'), new Table('a')),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   Table   $name       Expected property
	 */
	public function test_constructor($arguments, $name)
	{
		$class = new \ReflectionClass('SQL\DDL\Create_Table');
		$table = $class->newInstanceArgs($arguments);

		$this->assertEquals($name, $table->name);

		$this->assertSame('CREATE TABLE :name (:columns)', (string) $table);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$table->parameters
		);
	}

	public function provider_column()
	{
		return array(
			array(new Column, array(new Column)),
			array(new Expression('a'), array(new Expression('a'))),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   Column  $argument   Argument
	 * @param   array   $columns    Expected property
	 */
	public function test_column($argument, $columns)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->column($argument));
		$this->assertEquals($columns, $table->columns);

		$this->assertSame('CREATE TABLE :name (:columns)', (string) $table);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$table->parameters
		);
	}

	public function provider_columns()
	{
		return array(
			array(array(), NULL),
			array(array(new Column('a')), array(new Column('a'))),
			array(
				array(new Column('b'), new Column('c')),
				array(new Column('b'), new Column('c')),
			),
			array(array(new Expression('d')), array(new Expression('d'))),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $columns    Expected property
	 */
	public function test_columns($argument, $columns)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->columns($argument));
		$this->assertEquals($columns, $table->columns);

		$this->assertSame('CREATE TABLE :name (:columns)', (string) $table);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$table->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$table = new Create_Table;
		$table->columns($argument);

		$this->assertSame($table, $table->columns(NULL));
		$this->assertNull($table->columns);

		$this->assertSame('CREATE TABLE :name (:columns)', (string) $table);
		$this->assertSame($this->parameters, $table->parameters);
	}

	public function provider_constraint()
	{
		return array(
			array(new Constraint\Primary, array(new Constraint\Primary)),
			array(new Constraint\Unique, array(new Constraint\Unique)),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::constraint
	 *
	 * @dataProvider    provider_constraint
	 *
	 * @param   Constraint  $argument       Argument
	 * @param   array       $constraints    Expected property
	 */
	public function test_constraint($argument, $constraints)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->constraint($argument));
		$this->assertEquals($constraints, $table->constraints);

		$this->assertSame(
			'CREATE TABLE :name (:columns, :constraints)', (string) $table
		);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(':constraints' => $constraints)
			),
			$table->parameters
		);
	}

	public function provider_constraints()
	{
		return array(
			array(NULL, NULL, 'CREATE TABLE :name (:columns)'),
			array(array(), NULL, 'CREATE TABLE :name (:columns)'),
			array(
				array(new Constraint\Primary),
				array(new Constraint\Primary),
				'CREATE TABLE :name (:columns, :constraints)',
			),
			array(
				array(new Constraint\Primary, new Constraint\Unique),
				array(new Constraint\Primary, new Constraint\Unique),
				'CREATE TABLE :name (:columns, :constraints)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::constraints
	 *
	 * @dataProvider    provider_constraints
	 *
	 * @param   array   $argument       Argument
	 * @param   array   $constraints    Expected property
	 * @param   string  $value
	 */
	public function test_constraints($argument, $constraints, $value)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->constraints($constraints));
		$this->assertEquals($constraints, $table->constraints);

		$this->assertSame($value, (string) $table);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(':constraints' => $constraints)
			),
			$table->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::constraints
	 *
	 * @dataProvider    provider_constraints
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_constraints_reset($argument)
	{
		$table = new Create_Table;
		$table->constraints($argument);

		$this->assertSame($table, $table->constraints(NULL));
		$this->assertNull($table->constraints);

		$this->assertSame('CREATE TABLE :name (:columns)', (string) $table);
		$this->assertSame($this->parameters, $table->parameters);
	}

	public function provider_name()
	{
		return array(
			array('a', new Table('a')),
			array(array('a'), new Table('a')),
			array(new Expression('a'), new Expression('a')),
			array(new Identifier('a'), new Identifier('a')),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Table   $name       Expected
	 */
	public function test_name($argument, $name)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->name($argument));
		$this->assertEquals($name, $table->name);

		$this->assertSame('CREATE TABLE :name (:columns)', (string) $table);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$table->parameters
		);
	}

	public function provider_query()
	{
		return array(
			array(NULL, 'CREATE TABLE :name (:columns)'),
			array(new Expression('a'),  'CREATE TABLE :name AS (:query)'),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::query
	 *
	 * @dataProvider    provider_query
	 *
	 * @param   Expression  $query  Argument
	 * @param   string      $value
	 */
	public function test_query($query, $value)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->query($query));
		$this->assertSame($query, $table->query);

		$this->assertSame($value, (string) $table);
		$this->assertSame(
			array_merge($this->parameters, array(':query' => $query)),
			$table->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::query
	 *
	 * @dataProvider    provider_query
	 *
	 * @param   Expression  $query  Argument
	 */
	public function test_query_reset($query)
	{
		$table = new Create_Table;
		$table->query($query);

		$this->assertSame($table, $table->query(NULL));
		$this->assertNull($table->query);

		$this->assertSame('CREATE TABLE :name (:columns)', (string) $table);
		$this->assertSame($this->parameters, $table->parameters);
	}

	public function provider_temporary()
	{
		return array(
			array(array(), TRUE, 'CREATE TEMPORARY TABLE :name (:columns)'),
			array(array(TRUE), TRUE, 'CREATE TEMPORARY TABLE :name (:columns)'),
			array(array(FALSE), FALSE, 'CREATE TABLE :name (:columns)'),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::temporary
	 *
	 * @dataProvider    provider_temporary
	 *
	 * @param   array   $arguments  Arguments
	 * @param   boolean $temporary  Expected
	 * @param   string  $value
	 */
	public function test_temporary($arguments, $temporary, $value)
	{
		$table = new Create_Table;

		$this->assertSame(
			$table,
			call_user_func_array(array($table, 'temporary'), $arguments)
		);
		$this->assertSame($temporary, $table->temporary);

		$this->assertSame($value, (string) $table);
		$this->assertSame($this->parameters, $table->parameters);
	}

	/**
	 * @covers  SQL\DDL\Create_Table::__toString
	 */
	public function test_toString()
	{
		$table = new Create_Table;
		$table->temporary();

		$this->assertSame(
			'CREATE TEMPORARY TABLE :name (:columns)', (string) $table
		);

		$table->constraint(new Constraint\Primary(array('a')));

		$this->assertSame(
			'CREATE TEMPORARY TABLE :name (:columns, :constraints)',
			(string) $table
		);

		$table->query(new Expression('b'));

		$this->assertSame(
			'CREATE TEMPORARY TABLE :name AS (:query)', (string) $table
		);

		$table->column(new Column('c', 'd'));

		$this->assertSame(
			'CREATE TEMPORARY TABLE :name (:columns) AS (:query)',
			(string) $table
		);
	}
}
