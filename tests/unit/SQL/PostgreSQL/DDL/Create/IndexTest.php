<?php
namespace SQL\PostgreSQL\DDL;

use SQL\Column as SQL_Column;
use SQL\Conditions;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
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
		':tablespace' => NULL,
		':where' => NULL,
		':with' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL, NULL),
			array(array('a'), new Identifier('a'), NULL, NULL),
			array(array('a', 'b'), new Identifier('a'), new Table('b'), NULL),
			array(
				array('a', 'b', array('c')),
				new Identifier('a'), new Table('b'), array(new SQL_Column('c')),
			),
			array(
				array('a', 'b', array('c', 'd')),
				new Identifier('a'), new Table('b'),
				array(new SQL_Column('c'), new SQL_Column('d')),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::__construct
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
		$class = new \ReflectionClass('SQL\PostgreSQL\DDL\Create_Index');
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
			array('a', NULL, NULL, array(new SQL_Column('a'))),
			array(
				'a', 'b', NULL,
				array(new Expression('? B', array(new SQL_Column('a')))),
			),
			array(
				'a', NULL, 'c',
				array(new Expression('? NULLS C', array(new SQL_Column('a')))),
			),
			array(
				'a', 'b', 'c',
				array(
					new Expression('? B NULLS C', array(new SQL_Column('a'))),
				),
			),

			array(new SQL_Column('a'), NULL, NULL, array(new SQL_Column('a'))),
			array(
				new SQL_Column('a'), 'b', NULL,
				array(new Expression('? B', array(new SQL_Column('a')))),
			),
			array(
				new SQL_Column('a'), NULL, 'c',
				array(new Expression('? NULLS C', array(new SQL_Column('a')))),
			),
			array(
				new SQL_Column('a'), 'b', 'c',
				array(
					new Expression('? B NULLS C', array(new SQL_Column('a'))),
				),
			),

			array(
				new Expression('a'), NULL, NULL,
				array(new Expression('(?)', array(new Expression('a')))),
			),
			array(
				new Expression('a'), 'b', NULL,
				array(new Expression('(?) B', array(new Expression('a')))),
			),
			array(
				new Expression('a'), NULL, 'c',
				array(
					new Expression('(?) NULLS C', array(new Expression('a'))),
				),
			),
			array(
				new Expression('a'), 'b', 'c',
				array(
					new Expression('(?) B NULLS C', array(new Expression('a'))),
				),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   mixed   $column     First argument
	 * @param   string  $direction  Second argument
	 * @param   string  $nulls      Third argument
	 * @param   array   $columns    Expected property
	 */
	public function test_column($column, $direction, $nulls, $columns)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->column($column, $direction, $nulls));
		$this->assertEquals($columns, $index->columns);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$index->parameters
		);
	}

	public function test_column_defaults()
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->column('a'));
		$this->assertEquals(array(new SQL_Column('a')), $index->columns);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(':columns' => array(new SQL_Column('a')))
			),
			$index->parameters
		);


		$index = new Create_Index;

		$this->assertSame($index, $index->column('a', 'b'));
		$this->assertEquals(
			array(new Expression('? B', array(new SQL_Column('a')))),
			$index->columns
		);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(
					':columns' => array(
						new Expression('? B', array(new SQL_Column('a'))),
					),
				)
			),
			$index->parameters
		);
	}

	public function provider_tablespace()
	{
		return array(
			array(NULL, NULL, 'CREATE INDEX :name ON :table (:columns)'),
			array(
				'a', new Identifier('a'),
				'CREATE INDEX :name ON :table (:columns) TABLESPACE :tablespace',
			),
			array(
				new Identifier('a'), new Identifier('a'),
				'CREATE INDEX :name ON :table (:columns) TABLESPACE :tablespace',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::tablespace
	 *
	 * @dataProvider    provider_tablespace
	 *
	 * @param   mixed       $argument   Argument
	 * @param   Identifier  $tablespace Expected property
	 * @param   string      $value
	 */
	public function test_tablespace($argument, $tablespace, $value)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->tablespace($argument));
		$this->assertEquals($tablespace, $index->tablespace);

		$this->assertSame($value, (string) $index);
		$this->assertEquals(
			array_merge($this->parameters, array(':tablespace' => $tablespace)),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::tablespace
	 *
	 * @dataProvider    provider_tablespace
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_tablespace_reset($argument)
	{
		$index = new Create_Index;
		$index->tablespace($argument);

		$this->assertSame($index, $index->tablespace(NULL));
		$this->assertNull($index->tablespace);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertSame($this->parameters, $index->parameters);
	}

	public function provider_using()
	{
		return array(
			array(NULL, 'CREATE INDEX :name ON :table (:columns)'),
			array('a', 'CREATE INDEX :name ON :table USING a (:columns)'),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::using
	 *
	 * @dataProvider    provider_using
	 *
	 * @param   string  $argument   Argument
	 * @param   string  $value
	 */
	public function test_using($argument, $value)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->using($argument));
		$this->assertSame($argument, $index->using);

		$this->assertSame($value, (string) $index);
		$this->assertSame($this->parameters, $index->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::using
	 *
	 * @dataProvider    provider_using
	 *
	 * @param   string  $argument   Argument
	 */
	public function test_using_reset($argument)
	{
		$index = new Create_Index;
		$index->using($argument);

		$this->assertSame($index, $index->using(NULL));
		$this->assertNull($index->using);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertSame($this->parameters, $index->parameters);
	}

	public function provider_where()
	{
		return array(
			array(
				NULL, NULL, NULL,
				NULL, 'CREATE INDEX :name ON :table (:columns)',
			),
			array(
				'a', NULL, NULL,
				'a', 'CREATE INDEX :name ON :table (:columns) WHERE :where',
			),
			array(
				'a', 'b', NULL,
				new Conditions(new SQL_Column('a'), 'b', NULL),
				'CREATE INDEX :name ON :table (:columns) WHERE :where',
			),
			array(
				'a', 'b', 'c',
				new Conditions(new SQL_Column('a'), 'b', 'c'),
				'CREATE INDEX :name ON :table (:columns) WHERE :where',
			),

			array(
				new Conditions, NULL, NULL,
				new Conditions,
				'CREATE INDEX :name ON :table (:columns) WHERE :where',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   mixed       $left       First argument
	 * @param   string      $operator   Second argument
	 * @param   mixed       $right      Third argument
	 * @param   Conditions  $where      Expected property
	 * @param   string      $value
	 */
	public function test_where($left, $operator, $right, $where, $value)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->where($left, $operator, $right));
		$this->assertEquals($where, $index->where);

		$this->assertSame($value, (string) $index);
		$this->assertEquals(
			array_merge($this->parameters, array(':where' => $where)),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::where
	 */
	public function test_where_defaults()
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->where('a'));
		$this->assertSame('a', $index->where);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns) WHERE :where',
			(string) $index
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':where' => 'a')),
			$index->parameters
		);


		$index = new Create_Index;

		$this->assertSame($index, $index->where('a', 'b'));
		$this->assertEquals(
			new Conditions(new SQL_Column('a'), 'b', NULL), $index->where
		);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns) WHERE :where',
			(string) $index
		);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(
					':where' => new Conditions(new SQL_Column('a'), 'b', NULL),
				)
			),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   mixed   $left       First argument
	 * @param   string  $operator   Second argument
	 * @param   mixed   $right      Third argument
	 */
	public function test_where_reset($left, $operator, $right)
	{
		$index = new Create_Index;
		$index->where($left, $operator, $right);

		$this->assertSame($index, $index->where(NULL));
		$this->assertNull($index->where);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertSame($this->parameters, $index->parameters);
	}

	public function provider_with()
	{
		return array(
			array(NULL, NULL, 'CREATE INDEX :name ON :table (:columns)'),
			array(
				array('a' => 'b'),
				new Parameters(array('a' => 'b')),
				'CREATE INDEX :name ON :table (:columns) WITH (:with)',
			),
			array(
				new Parameters(array('a' => 'b')),
				new Parameters(array('a' => 'b')),
				'CREATE INDEX :name ON :table (:columns) WITH (:with)',
			),
			array(
				new Expression('e'),
				new Expression('e'),
				'CREATE INDEX :name ON :table (:columns) WITH (:with)',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::with
	 *
	 * @dataProvider    provider_with
	 *
	 * @param   mixed       $argument   Argument
	 * @param   Parameters  $with       Expected property
	 * @param   string      $value
	 */
	public function test_with($argument, $with, $value)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->with($argument));
		$this->assertEquals($with, $index->with);

		$this->assertSame($value, (string) $index);
		$this->assertEquals(
			array_merge($this->parameters, array(':with' => $with)),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::with
	 *
	 * @dataProvider    provider_with
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_with_reset($argument)
	{
		$index = new Create_Index;
		$index->with($argument);

		$this->assertSame($index, $index->with(NULL));
		$this->assertNull($index->with);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertSame($this->parameters, $index->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Create_Index::__toString
	 */
	public function test_toString()
	{
		$index = new Create_Index;
		$index
			->unique()
			->using('a')
			->with(array('b' => 'c'))
			->tablespace('d')
			->where('e');

		$this->assertSame(
			'CREATE :type INDEX :name ON :table USING a (:columns) WITH (:with)'
			.' TABLESPACE :tablespace WHERE :where',
			(string) $index
		);
	}
}
