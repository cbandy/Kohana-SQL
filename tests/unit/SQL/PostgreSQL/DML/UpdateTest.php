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
class UpdateTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':from' => NULL,
		':limit' => NULL,
		':values' => NULL,
		':table' => NULL,
		':where' => NULL,
		':returning' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL),

			array(array('a'), new Table('a'), NULL),
			array(array('a', 'b'), new Alias(new Table('a'), 'b'), NULL),

			array(
				array(NULL, NULL, array('a' => 'b')),
				NULL,
				array(new Expression('? = ?', array(new Column('a'), 'b'))),
			),

			array(
				array('a', 'b', array('c' => 'd')),
				new Alias(new Table('a'), 'b'),
				array(new Expression('? = ?', array(new Column('c'), 'd'))),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Update::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array           $arguments  Arguments
	 * @param   Table_Reference $table      Expected property
	 * @param   array           $set        Expected property
	 */
	public function test_constructor($arguments, $table, $set)
	{
		$class = new \ReflectionClass('SQL\PostgreSQL\DML\Update');
		$update = $class->newInstanceArgs($arguments);

		$this->assertEquals($table, $update->table);
		$this->assertEquals($set, $update->set);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(':table' => $table, ':values' => $set)
			),
			$update->parameters
		);
	}

	public function provider_returning()
	{
		return array(
			array(NULL, NULL, 'UPDATE :table SET :values'),

			array(
				array('a'), array(new Column('a')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array('a', 'b'), array(new Column('a'), new Column('b')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array('a' => 'b'), array(new Alias(new Column('b'), 'a')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'UPDATE :table SET :values RETURNING :returning',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array('a' => new Column('b')),
				array(new Alias(new Column('b'), 'a')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array('a' => new Column('b'), 'c' => new Column('d')),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'UPDATE :table SET :values RETURNING :returning',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array('a' => new Expression('b')),
				array(new Alias(new Expression('b'), 'a')),
				'UPDATE :table SET :values RETURNING :returning',
			),
			array(
				array('a' => new Expression('b'), 'c' => new Expression('d')),
				array(
					new Alias(new Expression('b'), 'a'),
					new Alias(new Expression('d'), 'c'),
				),
				'UPDATE :table SET :values RETURNING :returning',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Update::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $returning  Expected property
	 * @param   string  $value
	 */
	public function test_returning($argument, $returning, $value)
	{
		$update = new Update;

		$this->assertSame($update, $update->returning($argument));
		$this->assertEquals($returning, $update->returning);

		$this->assertSame($value, (string) $update);
		$this->assertEquals(
			array_merge($this->parameters, array(':returning' => $returning)),
			$update->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Update::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   array   $argument
	 */
	public function test_returning_reset($argument)
	{
		$update = new Update;
		$update->returning($argument);

		$this->assertSame($update, $update->returning(NULL));
		$this->assertNull($update->returning);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertSame($this->parameters, $update->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Update::__toString
	 */
	public function test_toString()
	{
		$update = new Update;
		$update
			->table('a')
			->set(array('b' => 0))
			->from('c')
			->where('d', '=', 1)
			->limit(2)
			->returning(array('e'));

		$this->assertSame(
			'UPDATE :table SET :values FROM :from WHERE :where LIMIT :limit RETURNING :returning',
			(string) $update
		);
	}
}
