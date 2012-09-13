<?php
namespace SQL\DDL\Constraint;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ForeignTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':name' => NULL,
		':columns' => NULL,
		':referencing' => NULL,
		':table' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL, 'REFERENCES :table'),
			array(array('a'), new Table('a'), NULL, 'REFERENCES :table'),
			array(
				array('a', array('b')),
				new Table('a'), array(new Column('b')),
				'REFERENCES :table (:columns)',
			),
			array(
				array('a', array('b', 'c')),
				new Table('a'), array(new Column('b'), new Column('c')),
				'REFERENCES :table (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   Table   $table      Expected property
	 * @param   array   $columns    Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $table, $columns, $value)
	{
		$class = new \ReflectionClass('SQL\DDL\Constraint\Foreign');
		$foreign = $class->newInstanceArgs($arguments);

		$this->assertEquals($table, $foreign->table);
		$this->assertEquals($columns, $foreign->columns);

		$this->assertSame($value, (string) $foreign);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(':columns' => $columns, ':table' => $table)
			),
			$foreign->parameters
		);
	}

	public function provider_columns()
	{
		return array(
			array(NULL, NULL, 'REFERENCES :table'),

			array(
				array('a'),
				array(new Column('a')), 'REFERENCES :table (:columns)',
			),
			array(
				array('a', 'b'),
				array(new Column('a'), new Column('b')),
				'REFERENCES :table (:columns)',
			),

			array(
				array(new Column('a')),
				array(new Column('a')), 'REFERENCES :table (:columns)',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'REFERENCES :table (:columns)',
			),

			array(
				array(new Expression('a')),
				array(new Expression('a')), 'REFERENCES :table (:columns)',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'REFERENCES :table (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $columns    Expected
	 * @param   string  $value
	 */
	public function test_columns($arguments, $columns, $value)
	{
		$foreign = new Foreign;

		$this->assertSame($foreign, $foreign->columns($arguments));
		$this->assertEquals($columns, $foreign->columns);

		$this->assertSame($value, (string) $foreign);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$foreign->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$foreign = new Foreign;
		$foreign->columns($argument);

		$this->assertSame($foreign, $foreign->columns(NULL));
		$this->assertNull($foreign->columns);

		$this->assertSame('REFERENCES :table', (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	public function provider_deferrable()
	{
		return array(
			array(NULL, 'REFERENCES :table'),
			array(FALSE, 'REFERENCES :table NOT DEFERRABLE'),
			array(TRUE, 'REFERENCES :table DEFERRABLE'),
			array(
				'immediate',
				'REFERENCES :table DEFERRABLE INITIALLY IMMEDIATE',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::deferrable
	 *
	 * @dataProvider    provider_deferrable
	 *
	 * @param   mixed   $argument   Argument
	 * @param   string  $value
	 */
	public function test_deferrable($argument, $value)
	{
		$foreign = new Foreign;

		$this->assertSame($foreign, $foreign->deferrable($argument));
		$this->assertSame($value, (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::deferrable
	 *
	 * @dataProvider    provider_deferrable
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_deferrable_reset($argument)
	{
		$foreign = new Foreign;
		$foreign->deferrable($argument);

		$this->assertSame($foreign, $foreign->deferrable(NULL));
		$this->assertSame('REFERENCES :table', (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	public function provider_match()
	{
		return array(
			array(NULL, 'REFERENCES :table'),
			array('', 'REFERENCES :table'),
			array('full', 'REFERENCES :table MATCH FULL'),
			array('other', 'REFERENCES :table MATCH OTHER'),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::match
	 *
	 * @dataProvider    provider_match
	 *
	 * @param   string  $argument   Argument
	 * @param   string  $value
	 */
	public function test_match($argument, $value)
	{
		$foreign = new Foreign;

		$this->assertSame($foreign, $foreign->match($argument));
		$this->assertSame($value, (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::match
	 *
	 * @dataProvider    provider_match
	 *
	 * @param   string  $argument   Argument
	 */
	public function test_match_reset($argument)
	{
		$foreign = new Foreign;
		$foreign->match($argument);

		$this->assertSame($foreign, $foreign->match(NULL));
		$this->assertSame('REFERENCES :table', (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	public function provider_on()
	{
		return array(
			array('delete', NULL, 'REFERENCES :table'),
			array('delete', 'cascade', 'REFERENCES :table ON DELETE CASCADE'),
			array('delete', 'other', 'REFERENCES :table ON DELETE OTHER'),

			array('update', NULL, 'REFERENCES :table'),
			array('update', 'set null', 'REFERENCES :table ON UPDATE SET NULL'),
			array('update', 'other', 'REFERENCES :table ON UPDATE OTHER'),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::on
	 *
	 * @dataProvider    provider_on
	 *
	 * @param   string  $event  First argument
	 * @param   string  $action Second argument
	 * @param   string  $value
	 */
	public function test_on($event, $action, $value)
	{
		$foreign = new Foreign;

		$this->assertSame($foreign, $foreign->on($event, $action));
		$this->assertSame($value, (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::on
	 *
	 * @dataProvider    provider_on
	 *
	 * @param   string  $event  First argument
	 * @param   string  $action Second argument
	 */
	public function test_on_reset($event, $action)
	{
		$foreign = new Foreign;
		$foreign->on($event, $action);

		$this->assertSame($foreign, $foreign->on($event, NULL));
		$this->assertSame('REFERENCES :table', (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	public function provider_referencing()
	{
		return array(
			array(NULL, NULL, 'REFERENCES :table'),

			array(
				array('a'), array(new Column('a')),
				'FOREIGN KEY (:referencing) REFERENCES :table',
			),
			array(
				array('a', 'b'),
				array(new Column('a'), new Column('b')),
				'FOREIGN KEY (:referencing) REFERENCES :table',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'FOREIGN KEY (:referencing) REFERENCES :table',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'FOREIGN KEY (:referencing) REFERENCES :table',
			),

			array(
				array(new Expression('a')),
				array(new Expression('a')),
				'FOREIGN KEY (:referencing) REFERENCES :table',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'FOREIGN KEY (:referencing) REFERENCES :table',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::referencing
	 *
	 * @dataProvider    provider_referencing
	 *
	 * @param   array   $argument       Argument
	 * @param   array   $referencing    Expected
	 * @param   string  $value
	 */
	public function test_referencing($argument, $referencing, $value)
	{
		$foreign = new Foreign;

		$this->assertSame($foreign, $foreign->referencing($argument));
		$this->assertEquals($referencing, $foreign->referencing);

		$this->assertSame($value, (string) $foreign);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(':referencing' => $referencing)
			),
			$foreign->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::referencing
	 *
	 * @dataProvider    provider_referencing
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_referencing_reset($argument)
	{
		$foreign = new Foreign;
		$foreign->referencing($argument);

		$this->assertSame($foreign, $foreign->referencing(NULL));
		$this->assertNull($foreign->referencing);

		$this->assertSame('REFERENCES :table', (string) $foreign);
		$this->assertSame($this->parameters, $foreign->parameters);
	}

	public function provider_table()
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
	 * @covers  SQL\DDL\Constraint\Foreign::table
	 *
	 * @dataProvider    provider_table
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Table   $table      Expected
	 */
	public function test_table($argument, $table)
	{
		$foreign = new Foreign;

		$this->assertSame($foreign, $foreign->table($argument));
		$this->assertEquals($table, $foreign->table);

		$this->assertSame('REFERENCES :table', (string) $foreign);
		$this->assertEquals(
			array_merge($this->parameters, array(':table' => $table)),
			$foreign->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Constraint\Foreign::__toString
	 */
	public function test_toString()
	{
		$foreign = new Foreign;
		$foreign
			->name('a')
			->referencing(array('b'))
			->table('c')
			->columns(array('d'))
			->match('e')
			->on('delete', 'f')
			->on('update', 'g');

		$this->assertSame(
			'CONSTRAINT :name FOREIGN KEY (:referencing) REFERENCES :table (:columns) MATCH E ON DELETE F ON UPDATE G',
			(string) $foreign
		);

		$foreign->deferrable(FALSE);

		$this->assertSame(
			'CONSTRAINT :name FOREIGN KEY (:referencing) REFERENCES :table (:columns) MATCH E ON DELETE F ON UPDATE G NOT DEFERRABLE',
			(string) $foreign
		);

		$foreign->deferrable('h');

		$this->assertSame(
			'CONSTRAINT :name FOREIGN KEY (:referencing) REFERENCES :table (:columns) MATCH E ON DELETE F ON UPDATE G DEFERRABLE INITIALLY H',
			(string) $foreign
		);
	}
}
