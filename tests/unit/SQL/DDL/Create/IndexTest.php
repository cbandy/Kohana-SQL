<?php
namespace SQL\DDL;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Create_IndexTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':columns' => NULL,
		':name' => NULL,
		':table' => NULL,
		':type' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL, NULL),
			array(array('a'), new Identifier('a'), NULL, NULL),
			array(array('a', 'b'), new Identifier('a'), new Table('b'), NULL),
			array(
				array('a', 'b', array('c')),
				new Identifier('a'), new Table('b'), array(new Column('c')),
			),
			array(
				array('a', 'b', array('c', 'd')),
				new Identifier('a'), new Table('b'),
				array(new Column('c'), new Column('d')),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Identifier  $name       Expected property
	 * @param   Table       $on         Expected property
	 * @param   array       $columns    Expected property
	 */
	public function test_constructor($arguments, $name, $on, $columns)
	{
		$class = new \ReflectionClass('SQL\DDL\Create_Index');
		$index = $class->newInstanceArgs($arguments);

		$this->assertEquals($columns, $index->columns);
		$this->assertEquals($name, $index->name);
		$this->assertEquals($on, $index->on);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(':columns' => $columns, ':name' => $name, ':table' => $on)
			),
			$index->parameters
		);
	}

	public function provider_column()
	{
		return array(
			array(
				array('a'),
				array(new Column('a')),
			),
			array(
				array('a', 'b'),
				array(new Expression('? B', array(new Column('a')))),
			),

			array(
				array(new Column('c')),
				array(new Column('c')),
			),
			array(
				array(new Column('c'), 'd'),
				array(new Expression('? D', array(new Column('c')))),
			),

			array(
				array(new Expression('expr')),
				array(new Expression('expr')),
			),
			array(
				array(new Expression('expr'), 'f'),
				array(new Expression('? F', array(new Expression('expr')))),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $columns    Expected
	 */
	public function test_column($arguments, $columns)
	{
		$index = new Create_Index;

		$this->assertSame(
			$index, call_user_func_array(array($index, 'column'), $arguments)
		);
		$this->assertEquals($columns, $index->columns);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$index->parameters
		);
	}

	public function provider_columns()
	{
		return array(
			array(NULL, NULL),

			array(
				array('a'),
				array(new Column('a')),
			),
			array(
				array('a', 'b'),
				array(new Column('a'), new Column('b')),
			),

			array(
				array(new Column('a')),
				array(new Column('a')),
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
			),

			array(
				array(new Expression('a')),
				array(new Expression('a')),
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $columns    Expected
	 */
	public function test_columns($argument, $columns)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->columns($argument));
		$this->assertEquals($columns, $index->columns);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$index = new Create_Index;
		$index->columns($argument);

		$this->assertSame($index, $index->columns(NULL));
		$this->assertNull($index->columns);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals($this->parameters, $index->parameters);
	}

	public function provider_name()
	{
		return array(
			array('a', new Identifier('a')),
			array(array('a'), new Identifier('a')),
			array(new Expression('a'), new Expression('a')),
			array(new Identifier('a'), new Identifier('a')),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed       $argument   Argument
	 * @param   Identifier  $name       Expected
	 */
	public function test_name($argument, $name)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->name($argument));
		$this->assertEquals($name, $index->name);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$index->parameters
		);
	}

	public function provider_on()
	{
		return array(
			array('a', new Table('a')),
			array(array('a'), new Table('a')),
			array(new Expression('a'), new Expression('a')),
			array(new Identifier('a'), new Identifier('a')),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::on
	 *
	 * @dataProvider    provider_on
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Table   $on         Expected
	 */
	public function test_on($argument, $on)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->on($argument));
		$this->assertEquals($on, $index->on);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':table' => $on)),
			$index->parameters
		);
	}

	public function provider_unique()
	{
		return array(
			array(
				array(), new Expression('UNIQUE'),
				'CREATE :type INDEX :name ON :table (:columns)',
			),
			array(
				array(NULL), NULL,
				'CREATE INDEX :name ON :table (:columns)',
			),
			array(
				array(FALSE), NULL,
				'CREATE INDEX :name ON :table (:columns)',
			),
			array(
				array(TRUE), new Expression('UNIQUE'),
				'CREATE :type INDEX :name ON :table (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::unique
	 *
	 * @dataProvider    provider_unique
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Expression  $type       Expected
	 * @param   string      $value      Expected
	 */
	public function test_unique($arguments, $type, $value)
	{
		$index = new Create_Index;

		$this->assertSame(
			$index, call_user_func_array(array($index, 'unique'), $arguments)
		);
		$this->assertEquals($type, $index->type);

		$this->assertSame($value, (string) $index);
		$this->assertEquals(
			array_merge($this->parameters, array(':type' => $type)),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Create_Index::__toString
	 */
	public function test_toString()
	{
		$index = new Create_Index;
		$index->unique();

		$this->assertSame(
			'CREATE :type INDEX :name ON :table (:columns)', (string) $index
		);
	}
}
