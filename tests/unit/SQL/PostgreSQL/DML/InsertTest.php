<?php
namespace SQL\PostgreSQL\DML;

use SQL\Alias;
use SQL\Column;
use SQL\Expression;
use SQL\Table;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
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
		':returning' => NULL,
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
	 * @covers  SQL\PostgreSQL\DML\Insert::__construct
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
		$class = new \ReflectionClass('SQL\PostgreSQL\DML\Insert');
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

	public function provider_returning()
	{
		return array(
			array(NULL, NULL, 'INSERT INTO :table DEFAULT VALUES'),

			array(
				array('a'), array(new Column('a')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array('a', 'b'), array(new Column('a'), new Column('b')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array('a' => 'b'), array(new Alias(new Column('b'), 'a')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array('a' => new Column('b')),
				array(new Alias(new Column('b'), 'a')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array('a' => new Column('b'), 'c' => new Column('d')),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array('a' => new Expression('b')),
				array(new Alias(new Expression('b'), 'a')),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
			array(
				array('a' => new Expression('b'), 'c' => new Expression('d')),
				array(
					new Alias(new Expression('b'), 'a'),
					new Alias(new Expression('d'), 'c'),
				),
				'INSERT INTO :table DEFAULT VALUES RETURNING :returning',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Insert::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $returning  Expected property
	 * @param   string  $value
	 */
	public function test_returning($argument, $returning, $value)
	{
		$insert = new Insert;

		$this->assertSame($insert, $insert->returning($argument));
		$this->assertEquals($returning, $insert->returning);

		$this->assertSame($value, (string) $insert);
		$this->assertEquals(
			array_merge($this->parameters, array(':returning' => $returning)),
			$insert->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Insert::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   array   $argument
	 */
	public function test_returning_reset($argument)
	{
		$insert = new Insert;
		$insert->returning($argument);

		$this->assertSame($insert, $insert->returning(NULL));
		$this->assertNull($insert->returning);

		$this->assertSame('INSERT INTO :table DEFAULT VALUES', (string) $insert);
		$this->assertSame($this->parameters, $insert->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Insert::__toString
	 */
	public function test_toString()
	{
		$insert = new Insert;
		$insert
			->into('a')
			->columns(array('b'))
			->returning(array('c'));

		$this->assertSame(
			'INSERT INTO :table (:columns) DEFAULT VALUES RETURNING :returning',
			(string) $insert
		);

		$insert->values(array('d'));

		$this->assertSame(
			'INSERT INTO :table (:columns) :values RETURNING :returning',
			(string) $insert
		);
	}
}
