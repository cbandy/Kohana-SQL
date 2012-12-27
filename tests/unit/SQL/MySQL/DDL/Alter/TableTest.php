<?php
namespace SQL\MySQL\DDL;

use SQL\Column as SQL_Column;
use SQL\DDL\Column;
use SQL\Expression;
use SQL\Identifier;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Alter_TableTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':actions' => NULL,
		':name' => NULL,
	);

	public function provider_add_column()
	{
		return array(
			array(
				new Column, NULL,
				array(new Expression('ADD ?', array(new Column))),
			),
			array(
				new Column, FALSE,
				array(new Expression('ADD ?', array(new Column))),
			),
			array(
				new Column, TRUE,
				array(new Expression('ADD ? FIRST', array(new Column))),
			),
			array(
				new Column, 'a',
				array(
					new Expression(
						'ADD ? AFTER ?',
						array(new Column, new SQL_Column('a'))
					),
				),
			),
			array(
				new Column, new SQL_Column('b'),
				array(
					new Expression(
						'ADD ? AFTER ?',
						array(new Column, new SQL_Column('b'))
					),
				),
			),
			array(
				new Column, new Expression('c'),
				array(
					new Expression(
						'ADD ? AFTER ?',
						array(new Column, new Expression('c'))
					),
				),
			),
			array(
				new Column, new Identifier('d'),
				array(
					new Expression(
						'ADD ? AFTER ?',
						array(new Column, new Identifier('d'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Alter_Table::add_column
	 * @covers  SQL\MySQL\DDL\Alter_Table::position
	 *
	 * @dataProvider    provider_add_column
	 *
	 * @param   Column  $column     First argument
	 * @param   mixed   $after      Second argument
	 * @param   array   $actions    Expected property
	 */
	public function test_add_column($column, $after, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->add_column($column, $after));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Alter_Table::add_column
	 */
	public function test_add_column_default()
	{
		$alter = new Alter_Table;
		$column = new Column;
		$actions = array(new Expression('ADD ?', array($column)));

		$this->assertSame($alter, $alter->add_column($column));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_change_column()
	{
		return array(
			// data set #0
			array(
				'a', new Column, NULL,
				array(
					new Expression(
						'CHANGE ? ?', array(new SQL_Column('a'), new Column)
					),
				),
			),
			array(
				'a', new Column, FALSE,
				array(
					new Expression(
						'CHANGE ? ?', array(new SQL_Column('a'), new Column)
					),
				),
			),
			array(
				'a', new Column, TRUE,
				array(
					new Expression(
						'CHANGE ? ? FIRST',
						array(new SQL_Column('a'), new Column)
					),
				),
			),
			array(
				'a', new Column, 'b',
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('a'),
							new Column,
							new SQL_Column('b'),
						)
					),
				),
			),
			array(
				'a', new Column, new SQL_Column('b'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('a'),
							new Column,
							new SQL_Column('b'),
						)
					),
				),
			),
			array(
				'a', new Column, new Expression('b'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('a'),
							new Column,
							new Expression('b'),
						)
					),
				),
			),
			array(
				'a', new Column, new Identifier('b'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('a'),
							new Column,
							new Identifier('b'),
						)
					),
				),
			),

			// data set #7
			array(
				new SQL_Column('c'), new Column, NULL,
				array(
					new Expression(
						'CHANGE ? ?', array(new SQL_Column('c'), new Column)
					),
				),
			),
			array(
				new SQL_Column('c'), new Column, FALSE,
				array(
					new Expression(
						'CHANGE ? ?', array(new SQL_Column('c'), new Column)
					),
				),
			),
			array(
				new SQL_Column('c'), new Column, TRUE,
				array(
					new Expression(
						'CHANGE ? ? FIRST',
						array(new SQL_Column('c'), new Column)
					),
				),
			),
			array(
				new SQL_Column('c'), new Column, 'd',
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('c'),
							new Column,
							new SQL_Column('d'),
						)
					),
				),
			),
			array(
				new SQL_Column('c'), new Column, new SQL_Column('d'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('c'),
							new Column,
							new SQL_Column('d'),
						)
					),
				),
			),
			array(
				new SQL_Column('c'), new Column, new Expression('d'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('c'),
							new Column,
							new Expression('d'),
						)
					),
				),
			),
			array(
				new SQL_Column('c'), new Column, new Identifier('d'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new SQL_Column('c'),
							new Column,
							new Identifier('d'),
						)
					),
				),
			),

			// data set #14
			array(
				new Expression('e'), new Column, NULL,
				array(
					new Expression(
						'CHANGE ? ?', array(new Expression('e'), new Column)
					),
				),
			),
			array(
				new Expression('e'), new Column, FALSE,
				array(
					new Expression(
						'CHANGE ? ?', array(new Expression('e'), new Column)
					),
				),
			),
			array(
				new Expression('e'), new Column, TRUE,
				array(
					new Expression(
						'CHANGE ? ? FIRST',
						array(new Expression('e'), new Column)
					),
				),
			),
			array(
				new Expression('e'), new Column, 'f',
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Expression('e'),
							new Column,
							new SQL_Column('f'),
						)
					),
				),
			),
			array(
				new Expression('e'), new Column, new SQL_Column('f'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Expression('e'),
							new Column,
							new SQL_Column('f'),
						)
					),
				),
			),
			array(
				new Expression('e'), new Column, new Expression('f'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Expression('e'),
							new Column,
							new Expression('f'),
						)
					),
				),
			),
			array(
				new Expression('e'), new Column, new Identifier('f'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Expression('e'),
							new Column,
							new Identifier('f'),
						)
					),
				),
			),

			// data set #21
			array(
				new Identifier('g'), new Column, NULL,
				array(
					new Expression(
						'CHANGE ? ?', array(new Identifier('g'), new Column)
					),
				),
			),
			array(
				new Identifier('g'), new Column, FALSE,
				array(
					new Expression(
						'CHANGE ? ?', array(new Identifier('g'), new Column)
					),
				),
			),
			array(
				new Identifier('g'), new Column, TRUE,
				array(
					new Expression(
						'CHANGE ? ? FIRST',
						array(new Identifier('g'), new Column)
					),
				),
			),
			array(
				new Identifier('g'), new Column, 'h',
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Identifier('g'),
							new Column,
							new SQL_Column('h'),
						)
					),
				),
			),
			array(
				new Identifier('g'), new Column, new SQL_Column('h'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Identifier('g'),
							new Column,
							new SQL_Column('h'),
						)
					),
				),
			),
			array(
				new Identifier('g'), new Column, new Expression('h'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Identifier('g'),
							new Column,
							new Expression('h'),
						)
					),
				),
			),
			array(
				new Identifier('g'), new Column, new Identifier('h'),
				array(
					new Expression(
						'CHANGE ? ? AFTER ?',
						array(
							new Identifier('g'),
							new Column,
							new Identifier('h'),
						)
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Alter_Table::change_column
	 * @covers  SQL\MySQL\DDL\Alter_Table::position
	 *
	 * @dataProvider    provider_change_column
	 *
	 * @param   mixed   $name       First argument
	 * @param   Column  $column     Second argument
	 * @param   mixed   $after      Third argument
	 * @param   array   $actions    Expected property
	 */
	public function test_change_column($name, $column, $after, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->change_column($name, $column, $after));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Alter_Table::change_column
	 */
	public function test_change_column_default()
	{
		$alter = new Alter_Table;
		$column = new Column;
		$actions = array(
			new Expression('CHANGE ? ?', array(new SQL_Column('c'), $column)),
		);

		$this->assertSame($alter, $alter->change_column('c', $column));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_drop_constraint()
	{
		return array(
			array('primary', 'a', array(new Expression('DROP PRIMARY KEY'))),

			array(
				'foreign', 'b',
				array(
					new Expression(
						'DROP FOREIGN KEY ?', array(new Identifier('b'))
					),
				),
			),
			array(
				'foreign', new Expression('c'),
				array(
					new Expression(
						'DROP FOREIGN KEY ?', array(new Expression('c'))
					),
				),
			),
			array(
				'foreign', new Identifier('d'),
				array(
					new Expression(
						'DROP FOREIGN KEY ?', array(new Identifier('d'))
					),
				),
			),

			array(
				'unique', 'e',
				array(
					new Expression(
						'DROP INDEX ?', array(new Identifier('e'))
					),
				),
			),
			array(
				'unique', new Expression('f'),
				array(
					new Expression(
						'DROP INDEX ?', array(new Expression('f'))
					),
				),
			),
			array(
				'unique', new Identifier('g'),
				array(
					new Expression(
						'DROP INDEX ?', array(new Identifier('g'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Alter_Table::drop_constraint
	 *
	 * @dataProvider    provider_drop_constraint
	 *
	 * @param   string  $type       First argument
	 * @param   mixed   $name       Second argument
	 * @param   array   $actions    Expected property
	 */
	public function test_drop_constraint($type, $name, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->drop_constraint($type, $name));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_options()
	{
		return array(
			array(
				array('ENGINE' => 'InnoDB'),
				array(new Options(array('ENGINE' => 'InnoDB'))),
			),
			array(
				new Options(array('AUTO_INCREMENT' => 5)),
				array(new Options(array('AUTO_INCREMENT' => 5))),
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Alter_Table::options
	 *
	 * @dataProvider    provider_options
	 *
	 * @param   mixed   $argument   Argument
	 * @param   array   $actions    Expected property
	 */
	public function test_options($argument, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->options($argument));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}
}
