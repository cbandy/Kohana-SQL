<?php
namespace SQL\PostgreSQL\DDL;

use SQL\Column as SQL_Column;
use SQL\Expression;
use SQL\Identifier;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
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

	public function provider_drop_column()
	{
		return array(
			array(
				'a', NULL,
				array(
					new Expression('DROP COLUMN ?', array(new SQL_Column('a'))),
				),
			),
			array(
				'a', FALSE,
				array(
					new Expression(
						'DROP COLUMN ? RESTRICT',
						array(new SQL_Column('a'))
					),
				),
			),
			array(
				'a', TRUE,
				array(
					new Expression(
						'DROP COLUMN ? CASCADE',
						array(new SQL_Column('a'))
					),
				),
			),

			array(
				new SQL_Column('b'), NULL,
				array(
					new Expression('DROP COLUMN ?', array(new SQL_Column('b'))),
				),
			),
			array(
				new SQL_Column('b'), FALSE,
				array(
					new Expression(
						'DROP COLUMN ? RESTRICT',
						array(new SQL_Column('b'))
					),
				),
			),
			array(
				new SQL_Column('b'), TRUE,
				array(
					new Expression(
						'DROP COLUMN ? CASCADE',
						array(new SQL_Column('b'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::drop_column
	 *
	 * @dataProvider    provider_drop_column
	 *
	 * @param   mixed   $name       First argument
	 * @param   boolean $cascade    Second argument
	 * @param   array   $actions    Expected property
	 */
	public function test_drop_column($name, $cascade, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->drop_column($name, $cascade));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::drop_column
	 */
	public function test_drop_column_default()
	{
		$alter = new Alter_Table;
		$actions = array(
			new Expression('DROP COLUMN ?', array(new SQL_Column('a')))
		);

		$this->assertSame($alter, $alter->drop_column('a'));
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
				'a', NULL,
				array(
					new Expression(
						'DROP CONSTRAINT ?', array(new Identifier('a'))
					),
				),
			),
			array(
				'a', FALSE,
				array(
					new Expression(
						'DROP CONSTRAINT ? RESTRICT', array(new Identifier('a'))
					),
				),
			),
			array(
				'a', TRUE,
				array(
					new Expression(
						'DROP CONSTRAINT ? CASCADE', array(new Identifier('a'))
					),
				),
			),

			array(
				new Identifier('b'), NULL,
				array(
					new Expression(
						'DROP CONSTRAINT ?', array(new Identifier('b'))
					),
				),
			),
			array(
				new Identifier('b'), FALSE,
				array(
					new Expression(
						'DROP CONSTRAINT ? RESTRICT', array(new Identifier('b'))
					),
				),
			),
			array(
				new Identifier('b'), TRUE,
				array(
					new Expression(
						'DROP CONSTRAINT ? CASCADE', array(new Identifier('b'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::drop_constraint
	 *
	 * @dataProvider    provider_drop_constraint
	 *
	 * @param   mixed   $name       Second argument
	 * @param   boolean $cascade    Third argument
	 * @param   array   $actions    Expected property
	 */
	public function test_drop_constraint($name, $cascade, $actions)
	{
		$alter = new Alter_Table;
		$any = substr(md5(rand()), 0, rand(0, 30));

		$this->assertSame(
			$alter, $alter->drop_constraint($any, $name, $cascade)
		);
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::drop_constraint
	 */
	public function test_drop_constraint_default()
	{
		$alter = new Alter_Table;
		$any = substr(md5(rand()), 0, rand(0, 30));
		$actions = array(
			new Expression('DROP CONSTRAINT ?', array(new Identifier('a'))),
		);

		$this->assertSame($alter, $alter->drop_constraint($any, 'a'));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_rename_column()
	{
		return array(
			array(
				'a', 'b',
				array(
					new Expression(
						'RENAME ? TO ?',
						array(new SQL_Column('a'), new SQL_Column('b'))
					),
				),
			),
			array(
				new SQL_Column('a'), 'b',
				array(
					new Expression(
						'RENAME ? TO ?',
						array(new SQL_Column('a'), new SQL_Column('b'))
					),
				),
			),
			array(
				'a', new SQL_Column('b'),
				array(
					new Expression(
						'RENAME ? TO ?',
						array(new SQL_Column('a'), new SQL_Column('b'))
					),
				),
			),
			array(
				new SQL_Column('a'), new SQL_Column('b'),
				array(
					new Expression(
						'RENAME ? TO ?',
						array(new SQL_Column('a'), new SQL_Column('b'))
					),
				),
			),

			array(
				new Expression('e'), new Expression('f'),
				array(
					new Expression(
						'RENAME ? TO ?',
						array(new Expression('e'), new Expression('f'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::rename_column
	 *
	 * @dataProvider    provider_rename_column
	 *
	 * @param   mixed   $old_name   First argument
	 * @param   mixed   $new_name   Second argument
	 * @param   array   $actions    Expected property
	 */
	public function test_rename_column($old_name, $new_name, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->rename_column($old_name, $new_name));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_set_not_null()
	{
		return array(
			array(
				'a', FALSE,
				array(
					new Expression(
						'DROP NOT NULL ?', array(new SQL_Column('a'))
					),
				),
			),
			array(
				'a', TRUE,
				array(
					new Expression(
						'SET NOT NULL ?', array(new SQL_Column('a'))
					),
				),
			),
			array(
				new SQL_Column('a'), FALSE,
				array(
					new Expression(
						'DROP NOT NULL ?', array(new SQL_Column('a'))
					),
				),
			),
			array(
				new SQL_Column('a'), TRUE,
				array(
					new Expression(
						'SET NOT NULL ?', array(new SQL_Column('a'))
					),
				),
			),
			array(
				new Expression('e'), FALSE,
				array(
					new Expression(
						'DROP NOT NULL ?', array(new Expression('e'))
					),
				),
			),
			array(
				new Expression('e'), TRUE,
				array(
					new Expression(
						'SET NOT NULL ?', array(new Expression('e'))
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::set_not_null
	 *
	 * @dataProvider    provider_set_not_null
	 *
	 * @param   mixed   $name       First argument
	 * @param   boolean $value      Second argument
	 * @param   array   $actions    Expected property
	 */
	public function test_set_not_null($name, $value, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->set_not_null($name, $value));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::set_not_null
	 */
	public function test_not_null_default()
	{
		$alter = new Alter_Table;
		$actions = array(
			new Expression('SET NOT NULL ?', array(new SQL_Column('a'))),
		);

		$this->assertSame($alter, $alter->set_not_null('a'));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	public function provider_type()
	{
		return array(
			array(
				'a', 'b', NULL,
				array(
					new Expression(
						'ALTER ? TYPE ?',
						array(new SQL_Column('a'), new Expression('b'))
					),
				),
			),
			array(
				new SQL_Column('a'), 'b', NULL,
				array(
					new Expression(
						'ALTER ? TYPE ?',
						array(new SQL_Column('a'), new Expression('b'))
					),
				),
			),
			array(
				'a', new Expression('b'), NULL,
				array(
					new Expression(
						'ALTER ? TYPE ?',
						array(new SQL_Column('a'), new Expression('b'))
					),
				),
			),
			array(
				new Identifier('a'), new Expression('b'), NULL,
				array(
					new Expression(
						'ALTER ? TYPE ?',
						array(new Identifier('a'), new Expression('b'))
					),
				),
			),

			array(
				'a', 'b', 'c',
				array(
					new Expression(
						'ALTER ? TYPE ? USING ?',
						array(
							new SQL_Column('a'),
							new Expression('b'),
							new Expression('c'),
						)
					),
				),
			),
			array(
				new Identifier('a'), 'b', 'c',
				array(
					new Expression(
						'ALTER ? TYPE ? USING ?',
						array(
							new Identifier('a'),
							new Expression('b'),
							new Expression('c'),
						)
					),
				),
			),
			array(
				'a', 'b', new Expression('c'),
				array(
					new Expression(
						'ALTER ? TYPE ? USING ?',
						array(
							new SQL_Column('a'),
							new Expression('b'),
							new Expression('c'),
						)
					),
				),
			),
			array(
				new Identifier('a'), 'b', new Expression('c'),
				array(
					new Expression(
						'ALTER ? TYPE ? USING ?',
						array(
							new Identifier('a'),
							new Expression('b'),
							new Expression('c'),
						)
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::type
	 *
	 * @dataProvider    provider_type
	 *
	 * @param   mixed   $name       First argument
	 * @param   mixed   $type       Second argument
	 * @param   mixed   $using      Third argument
	 * @param   array   $actions    Expected property
	 */
	public function test_type($name, $type, $using, $actions)
	{
		$alter = new Alter_Table;

		$this->assertSame($alter, $alter->type($name, $type, $using));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Alter_Table::type
	 */
	public function test_type_default()
	{
		$alter = new Alter_Table;
		$actions = array(
			new Expression(
				'ALTER ? TYPE ?',
				array(new SQL_Column('a'), new Expression('b'))
			),
		);

		$this->assertSame($alter, $alter->type('a', 'b'));
		$this->assertEquals($actions, $alter->actions);

		$this->assertSame('ALTER TABLE :name :actions', (string) $alter);
		$this->assertEquals(
			array_merge($this->parameters, array(':actions' => $actions)),
			$alter->parameters
		);
	}
}
