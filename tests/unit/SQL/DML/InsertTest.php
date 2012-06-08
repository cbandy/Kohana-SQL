<?php
namespace SQL\DML;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;
use SQL\Values;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class InsertTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':columns' => NULL,
		':table' => NULL,
		':values' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL, 'INSERT INTO :table DEFAULT VALUES'),
			array(
				array('a'), new Table('a'), NULL,
				'INSERT INTO :table DEFAULT VALUES',
			),
			array(
				array('a', array('b')), new Table('a'), array(new Column('b')),
				'INSERT INTO :table (:columns) DEFAULT VALUES',
			),
		);
	}

	/**
	 * @covers  SQL\DML\Insert::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   Table   $into       Expected property
	 * @param   array   $columns    Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $into, $columns, $value)
	{
		$class = new \ReflectionClass('SQL\DML\Insert');
		$insert = $class->newInstanceArgs($arguments);

		$this->assertEquals($into, $insert->into);
		$this->assertEquals($columns, $insert->columns);

		$this->assertSame($value, (string) $insert);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(':table' => $into, ':columns' => $columns)
			),
			$insert->parameters
		);
	}

	public function provider_columns()
	{
		$result[] = array(
			NULL, NULL, 'INSERT INTO :table DEFAULT VALUES', $this->parameters
		);

		$columns = array(new Column('a'));
		$result[] = array(
			array('a'), $columns,
			'INSERT INTO :table (:columns) DEFAULT VALUES',
			array_merge($this->parameters, array(':columns' => $columns)),
		);

		$columns = array(new Column('a'), new Column('b'));
		$result[] = array(
			array('a', 'b'), $columns,
			'INSERT INTO :table (:columns) DEFAULT VALUES',
			array_merge($this->parameters, array(':columns' => $columns)),
		);

		$columns = array(new Column('a'));
		$result[] = array(
			$columns, $columns,
			'INSERT INTO :table (:columns) DEFAULT VALUES',
			array_merge($this->parameters, array(':columns' => $columns)),
		);

		$columns = array(new Column('a'), new Column('b'));
		$result[] = array(
			$columns, $columns,
			'INSERT INTO :table (:columns) DEFAULT VALUES',
			array_merge($this->parameters, array(':columns' => $columns)),
		);

		$columns = array(new Expression('a'));
		$result[] = array(
			$columns, $columns,
			'INSERT INTO :table (:columns) DEFAULT VALUES',
			array_merge($this->parameters, array(':columns' => $columns)),
		);

		$columns = array(new Expression('a'), new Expression('b'));
		$result[] = array(
			$columns, $columns,
			'INSERT INTO :table (:columns) DEFAULT VALUES',
			array_merge($this->parameters, array(':columns' => $columns)),
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Insert::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $columns    Expected
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_columns($argument, $columns, $value, $parameters)
	{
		$insert = new Insert;

		$this->assertSame($insert, $insert->columns($argument));
		$this->assertEquals($columns, $insert->columns);

		$this->assertSame($value, (string) $insert);
		$this->assertEquals($parameters, $insert->parameters);
	}

	/**
	 * @covers  SQL\DML\Insert::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$insert = new Insert;
		$insert->columns($argument);

		$insert->columns(NULL);

		$this->assertNull($insert->columns);
		$this->assertSame('INSERT INTO :table DEFAULT VALUES', (string) $insert);
		$this->assertEquals($this->parameters, $insert->parameters);
	}

	public function provider_into()
	{
		return array(
			array(array('a'), new Table('a')),
			array('a', new Table('a')),
			array(new Expression('a'), new Expression('a')),
			array(new Identifier('a'), new Identifier('a')),
			array(new Table('a'), new Table('a')),
		);
	}

	/**
	 * @covers  SQL\DML\Insert::into
	 *
	 * @dataProvider    provider_into
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Table   $into       Expected
	 */
	public function test_into($argument, $into)
	{
		$insert = new Insert;

		$this->assertSame($insert, $insert->into($argument));
		$this->assertEquals($into, $insert->into);

		$this->assertSame('INSERT INTO :table DEFAULT VALUES', (string) $insert);
		$this->assertEquals(
			array_merge($this->parameters, array(':table' => $into)),
			$insert->parameters
		);
	}

	public function provider_values()
	{
		return array(
			array(NULL, NULL, 'INSERT INTO :table DEFAULT VALUES'),
			array(
				array('a'), new Values(array('a')),
				'INSERT INTO :table :values',
			),
			array(
				new Values(array('a')), new Values(array('a')),
				'INSERT INTO :table :values',
			),
		);
	}

	/**
	 * @covers  SQL\DML\Insert::values
	 *
	 * @dataProvider    provider_values
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Values  $values     Expected
	 * @param   string  $value
	 */
	public function test_values($argument, $values, $value)
	{
		$insert = new Insert;

		$this->assertSame($insert, $insert->values($argument));
		$this->assertEquals($values, $insert->values);

		$this->assertSame($value, (string) $insert);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$insert->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Insert::values
	 *
	 * @dataProvider    provider_values
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_values_reset($argument)
	{
		$insert = new Insert;
		$insert->values($argument);

		$this->assertSame($insert, $insert->values(NULL));
		$this->assertNull($insert->values);

		$this->assertSame('INSERT INTO :table DEFAULT VALUES', (string) $insert);
		$this->assertEquals($this->parameters, $insert->parameters);
	}

	/**
	 * @covers  SQL\DML\Insert::__toString
	 */
	public function test_toString()
	{
		$insert = new Insert;
		$insert
			->into('a')
			->columns(array('b'));

		$this->assertSame(
			'INSERT INTO :table (:columns) DEFAULT VALUES', (string) $insert
		);

		$insert->values(array('c'));

		$this->assertSame(
			'INSERT INTO :table (:columns) :values', (string) $insert
		);
	}
}
