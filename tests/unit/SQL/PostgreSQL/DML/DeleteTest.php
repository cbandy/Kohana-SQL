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
class DeleteTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':table' => NULL,
		':limit' => NULL,
		':using' => NULL,
		':where' => NULL,
		':returning' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL),
			array(array('a'), new Table('a')),
			array(array('a', 'b'), new Alias(new Table('a'), 'b')),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Delete::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   mixed   $from       Expected property
	 */
	public function test_constructor($arguments, $from)
	{
		$class = new \ReflectionClass('SQL\PostgreSQL\DML\Delete');
		$delete = $class->newInstanceArgs($arguments);

		$this->assertEquals($from, $delete->from);

		$this->assertSame('DELETE FROM :table', (string) $delete);
		$this->assertEquals(
			array_merge($this->parameters, array(':table' => $from)),
			$delete->parameters
		);
	}

	public function provider_returning()
	{
		return array(
			array(NULL, NULL, 'DELETE FROM :table'),

			array(
				array('a'), array(new Column('a')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array('a', 'b'), array(new Column('a'), new Column('b')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array('a' => 'b'), array(new Alias(new Column('b'), 'a')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'DELETE FROM :table RETURNING :returning',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array('a' => new Column('b')),
				array(new Alias(new Column('b'), 'a')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array('a' => new Column('b'), 'c' => new Column('d')),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'DELETE FROM :table RETURNING :returning',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array('a' => new Expression('b')),
				array(new Alias(new Expression('b'), 'a')),
				'DELETE FROM :table RETURNING :returning',
			),
			array(
				array('a' => new Expression('b'), 'c' => new Expression('d')),
				array(
					new Alias(new Expression('b'), 'a'),
					new Alias(new Expression('d'), 'c'),
				),
				'DELETE FROM :table RETURNING :returning',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Delete::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $returning  Expected property
	 * @param   string  $value
	 */
	public function test_returning($argument, $returning, $value)
	{
		$delete = new Delete;

		$this->assertSame($delete, $delete->returning($argument));
		$this->assertEquals($returning, $delete->returning);

		$this->assertSame($value, (string) $delete);
		$this->assertEquals(
			array_merge($this->parameters, array(':returning' => $returning)),
			$delete->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Delete::returning
	 *
	 * @dataProvider    provider_returning
	 *
	 * @param   array   $argument
	 */
	public function test_returning_reset($argument)
	{
		$delete = new Delete;
		$delete->returning($argument);

		$this->assertSame($delete, $delete->returning(NULL));
		$this->assertNull($delete->returning);

		$this->assertSame('DELETE FROM :table', (string) $delete);
		$this->assertSame($this->parameters, $delete->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Delete::__toString
	 */
	public function test_toString()
	{
		$delete = new Delete;
		$delete
			->from('a')
			->using('b')
			->where('c', '=', 'd')
			->limit(1)
			->returning(array('e'));

		$this->assertSame(
			'DELETE FROM :table USING :using WHERE :where LIMIT :limit RETURNING :returning',
			(string) $delete
		);
	}
}
