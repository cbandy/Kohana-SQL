<?php
namespace SQL\MySQL\DDL;

use SQL\Column as SQL_Column;
use SQL\DDL\Constraint\Foreign;
use SQL\DDL\Constraint\Unique;
use SQL\Expression;
use SQL\Listing;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class ColumnTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':constraints' => NULL,
		':default' => NULL,
		':name' => NULL,
		':type' => NULL,
		':comment' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL),
			array(array('a'), new SQL_Column('a'), NULL),
			array(array('b', 'c'), new SQL_Column('b'), new Expression('c')),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array       $arguments  Arguments
	 * @param   SQL\Column  $name       Expected property
	 * @param   Expression  $type       Expected property
	 */
	public function test_constructor($arguments, $name, $type)
	{
		$class = new \ReflectionClass('SQL\MySQL\DDL\Column');
		$column = $class->newInstanceArgs($arguments);

		$this->assertEquals($name, $column->name);
		$this->assertEquals($type, $column->type);

		$this->assertSame(':name :type', (string) $column);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(':name' => $name, ':type' => $type)
			),
			$column->parameters
		);
	}

	public function provider_auto_increment()
	{
		return array(
			array(NULL, ':name :type'),
			array(FALSE, ':name :type'),
			array(TRUE, ':name :type AUTO_INCREMENT'),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::auto_increment
	 *
	 * @dataProvider    provider_auto_increment
	 *
	 * @param   boolean $argument   Argument
	 * @param   string  $value
	 */
	public function test_auto_increment($argument, $value)
	{
		$column = new Column;

		$this->assertSame($column, $column->auto_increment($argument));
		$this->assertSame($argument, $column->auto_increment);

		$this->assertSame($value, (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::auto_increment
	 */
	public function test_auto_increment_default()
	{
		$column = new Column;

		$this->assertSame($column, $column->auto_increment());
		$this->assertTrue($column->auto_increment);

		$this->assertSame(':name :type AUTO_INCREMENT', (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::auto_increment
	 *
	 * @dataProvider    provider_auto_increment
	 *
	 * @param   boolean $argument   Argument
	 */
	public function test_auto_increment_reset($argument)
	{
		$column = new Column;
		$column->auto_increment($argument);

		$this->assertSame($column, $column->auto_increment(NULL));
		$this->assertNull($column->auto_increment);

		$this->assertSame(':name :type', (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	public function provider_comment()
	{
		return array(
			array(NULL, ':name :type'),
			array('text', ':name :type COMMENT :comment'),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::comment
	 *
	 * @dataProvider    provider_comment
	 *
	 * @param   string  $argument   Argument
	 * @param   string  $value
	 */
	public function test_comment($argument, $value)
	{
		$column = new Column;

		$this->assertSame($column, $column->comment($argument));
		$this->assertSame($argument, $column->comment);

		$this->assertSame($value, (string) $column);
		$this->assertSame(
			array_merge($this->parameters, array(':comment' => $argument)),
			$column->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::comment
	 *
	 * @dataProvider    provider_comment
	 *
	 * @param   string  $argument   Argument
	 */
	public function test_comment_reset($argument)
	{
		$column = new Column;
		$column->comment($argument);

		$this->assertSame($column, $column->comment(NULL));
		$this->assertNull($column->comment);

		$this->assertSame(':name :type', (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	public function provider_constraints()
	{
		return array(
			array(new Foreign, new Foreign),
			array(new Unique, new Unique),
			array(new Expression('expr'), new Expression('expr')),
			array(
				array(new Foreign, new Unique),
				new Listing(' ', array(new Unique, new Foreign)),
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::constraints
	 *
	 * @dataProvider    provider_constraints
	 *
	 * @param   mixed   $argument       Argument
	 * @param   mixed   $constraints    Expected property
	 */
	public function test_constraints($argument, $constraints)
	{
		$column = new Column;

		$this->assertSame($column, $column->constraints($argument));
		$this->assertEquals($constraints, $column->constraints);

		$this->assertSame(':name :type :constraints', (string) $column);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(':constraints' => $constraints)
			),
			$column->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::constraints
	 *
	 * @dataProvider    provider_constraints
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_constraints_reset($argument)
	{
		$column = new Column;
		$column->constraints($argument);

		$this->assertSame($column, $column->constraints(NULL));
		$this->assertNull($column->constraints);

		$this->assertSame(':name :type', (string) $column);
		$this->assertSame($this->parameters, $column->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Column::__toString
	 */
	public function test_toString()
	{
		$column = new Column;
		$column
			->name('a')
			->type('b')
			->set_default('c')
			->not_null()
			->auto_increment()
			->comment('c')
			->constraints(new Unique);

		$this->assertSame(
			':name :type DEFAULT :default NOT NULL AUTO_INCREMENT'
			.' COMMENT :comment :constraints',
			(string) $column
		);
	}
}
