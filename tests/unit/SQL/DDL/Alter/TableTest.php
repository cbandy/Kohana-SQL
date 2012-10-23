<?php
namespace SQL\DDL;

use SQL\Column as SQL_Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package SQL
 * @author  Chris Bandy
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

	public function provider_constructor()
	{
		return array(
			array(array(), NULL),
			array(array(NULL), NULL),
			array(array('a'), new Table('a')),
			array(array(new Expression('b')), new Expression('b')),
			array(array(new Identifier('c')), new Identifier('c')),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   Table   $name       Expected property
	 */
	public function test_constructor($arguments, $name)
	{
		$class = new \ReflectionClass('SQL\DDL\Alter_Table');
		$alter = $class->newInstanceArgs($arguments);

		$this->assertEquals($name, $alter->name);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$alter->parameters
		);
	}

	public function provider_add_column()
	{
		return array(
			array(
				new Column,
				array(new Expression('ADD ?', array(new Column))),
			),
			array(
				new Expression('expr'),
				array(new Expression('ADD ?', array(new Expression('expr')))),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::add_column
	 *
	 * @dataProvider    provider_add_column
	 *
	 * @param   Column  $argument   Argument
	 * @param   array   $actions    Expected property
	 */
	public function test_add_column($argument, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->add_column($argument));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_add_constraint()
	{
		return array(
			array(
				new Constraint\Unique,
				array(new Expression('ADD ?', array(new Constraint\Unique))),
			),
			array(
				new Expression('expr'),
				array(new Expression('ADD ?', array(new Expression('expr')))),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::add_constraint
	 *
	 * @dataProvider    provider_add_constraint
	 *
	 * @param   Constraint  $argument   Argument
	 * @param   array       $actions    Expected property
	 */
	public function test_add_constraint($argument, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->add_constraint($argument));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_drop_column()
	{
		return array(
			array(
				'a',
				array(
					new Expression('DROP COLUMN ?', array(new SQL_Column('a'))),
				),
			),
			array(
				new Expression('b'),
				array(
					new Expression('DROP COLUMN ?', array(new Expression('b'))),
				),
			),
			array(
				new Identifier('c'),
				array(
					new Expression('DROP COLUMN ?', array(new Identifier('c'))),
				),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::drop_column
	 *
	 * @dataProvider    provider_drop_column
	 *
	 * @param   mixed   $argument   Argument
	 * @param   array   $actions    Expected property
	 */
	public function test_drop_column($argument, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->drop_column($argument));
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
			array(
				'check', 'a',
				array(
					new Expression(
						'DROP CONSTRAINT ?', array(new Identifier('a'))
					),
				),
			),
			array(
				'any', new Expression('b'),
				array(
					new Expression(
						'DROP CONSTRAINT ?', array(new Expression('b'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::drop_constraint
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

	public function provider_drop_default()
	{
		return array(
			array(
				'a',
				array(
					new Expression(
						'ALTER ? DROP DEFAULT', array(new SQL_Column('a'))
					),
				),
			),
			array(
				new Expression('b'),
				array(
					new Expression(
						'ALTER ? DROP DEFAULT', array(new Expression('b'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::drop_default
	 *
	 * @dataProvider    provider_drop_default
	 *
	 * @param   mixed   $name       Argument
	 * @param   array   $actions    Expected property
	 */
	public function test_drop_default($name, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->drop_default($name));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_name()
	{
		return array(
			array('a', new Table('a')),
			array(new Expression('b'), new Expression('b')),
			array(new Identifier('c'), new Identifier('c')),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Table   $name       Expected property
	 */
	public function test_name($argument, $name)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->name($argument));
		$this->assertEquals($name, $alter->name);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$alter->parameters
		);
	}

	public function provider_rename()
	{
		return array(
			array(
				'a',
				array(new Expression('RENAME TO ?', array(new Table('a')))),
			),
			array(
				new Expression('b'),
				array(
					new Expression('RENAME TO ?', array(new Expression('b'))),
				),
			),
			array(
				new Identifier('c'),
				array(
					new Expression('RENAME TO ?', array(new Identifier('c'))),
				),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::rename
	 *
	 * @dataProvider    provider_rename
	 *
	 * @param   mixed   $name       Argument
	 * @param   array   $actions    Expected property
	 */
	public function test_rename($name, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->rename($name));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_set_default()
	{
		return array(
			array(
				'a', new \stdClass,
				array(
					new Expression(
						'ALTER ? SET DEFAULT ?',
						array(new SQL_Column('a'), new \stdClass)
					),
				),
			),
			array(
				new Expression('b'), 0,
				array(
					new Expression(
						'ALTER ? SET DEFAULT ?', array(new Expression('b'), 0)
					),
				),
			),
			array(
				new Identifier('c'), 'd',
				array(
					new Expression(
						'ALTER ? SET DEFAULT ?', array(new Identifier('c'), 'd')
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Alter_Table::set_default
	 *
	 * @dataProvider    provider_set_default
	 *
	 * @param   mixed   $name       First argument
	 * @param   mixed   $value      Second argument
	 * @param   array   $actions    Expected property
	 */
	public function test_set_default($name, $value, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->set_default($name, $value));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}
}
