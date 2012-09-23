<?php
namespace SQL\DDL;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Create_ViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':columns' => NULL,
		':name' => NULL,
		':query' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL),
			array(array('a'), new Table('a'), NULL),
			array(
				array('a', new Expression('b')),
				new Table('a'), new Expression('b'),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Table       $name       Expected property
	 * @param   Expression  $query      Expected property
	 */
	public function test_constructor($arguments, $name, $query)
	{
		$class = new \ReflectionClass('SQL\DDL\Create_View');
		$view = $class->newInstanceArgs($arguments);

		$this->assertEquals($name, $view->name);
		$this->assertEquals($query, $view->query);

		$this->assertSame('CREATE VIEW :name AS :query', (string) $view);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(':name' => $name, ':query' => $query)
			),
			$view->parameters
		);
	}

	public function provider_column()
	{
		return array(
			array(NULL, array(new Column(NULL))),
			array('a', array(new Column('a'))),
			array(new Column('b'), array(new Column('b'))),
			array(new Expression('c'), array(new Expression('c'))),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   mixed   $argument   Argument
	 * @param   array   $columns    Expected
	 */
	public function test_column($argument, $columns)
	{
		$view = new Create_View;

		$this->assertSame($view, $view->column($argument));
		$this->assertEquals($columns, $view->columns);

		$this->assertSame(
			'CREATE VIEW :name (:columns) AS :query', (string) $view
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$view->parameters
		);
	}

	public function provider_columns()
	{
		return array(
			array(NULL, NULL, 'CREATE VIEW :name AS :query'),

			array(
				array('a'), array(new Column('a')),
				'CREATE VIEW :name (:columns) AS :query',
			),
			array(
				array('a', 'b'), array(new Column('a'), new Column('b')),
				'CREATE VIEW :name (:columns) AS :query',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'CREATE VIEW :name (:columns) AS :query',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'CREATE VIEW :name (:columns) AS :query',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'CREATE VIEW :name (:columns) AS :query',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'CREATE VIEW :name (:columns) AS :query',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $columns    Expected
	 * @param   string  $value
	 */
	public function test_columns($argument, $columns, $value)
	{
		$view = new Create_View;

		$this->assertSame($view, $view->columns($argument));
		$this->assertEquals($columns, $view->columns);

		$this->assertSame($value, (string) $view);
		$this->assertEquals(
			array_merge($this->parameters, array(':columns' => $columns)),
			$view->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$view = new Create_View;
		$view->columns($argument);

		$this->assertSame($view, $view->columns(NULL));
		$this->assertNull($view->columns);

		$this->assertSame('CREATE VIEW :name AS :query', (string) $view);
		$this->assertSame($this->parameters, $view->parameters);
	}

	public function provider_name()
	{
		return array(
			array('a', new Table('a')),
			array(array('a'), new Table('a')),
			array(new Expression('a'), new Expression('a')),
			array(new Identifier('a'), new Identifier('a')),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Table   $name       Expected
	 */
	public function test_name($argument, $name)
	{
		$view = new Create_View;

		$this->assertSame($view, $view->name($argument));
		$this->assertEquals($name, $view->name);

		$this->assertSame('CREATE VIEW :name AS :query', (string) $view);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$view->parameters
		);
	}

	public function provider_query()
	{
		return array(
			array(NULL),
			array(new \stdClass),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::query
	 *
	 * @dataProvider    provider_query
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_query($argument)
	{
		$view = new Create_View;

		$this->assertSame($view, $view->query($argument));
		$this->assertSame($argument, $view->query);

		$this->assertSame('CREATE VIEW :name AS :query', (string) $view);
		$this->assertSame(
			array_merge($this->parameters, array(':query' => $argument)),
			$view->parameters
		);
	}

	public function provider_replace()
	{
		return array(
			array(array(), TRUE, 'CREATE OR REPLACE VIEW :name AS :query'),
			array(array(NULL), NULL, 'CREATE VIEW :name AS :query'),
			array(array(FALSE), FALSE, 'CREATE VIEW :name AS :query'),
			array(array(TRUE), TRUE, 'CREATE OR REPLACE VIEW :name AS :query'),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::replace
	 *
	 * @dataProvider    provider_replace
	 *
	 * @param   array   $arguments  Arguments
	 * @param   boolean $replace    Expected
	 * @param   string  $value
	 */
	public function test_replace($arguments, $replace, $value)
	{
		$view = new Create_View;

		$this->assertSame(
			$view, call_user_func_array(array($view, 'replace'), $arguments)
		);
		$this->assertSame($replace, $view->replace);

		$this->assertSame($value, (string) $view);
		$this->assertSame($this->parameters, $view->parameters);
	}

	public function provider_temporary()
	{
		return array(
			array(array(), TRUE, 'CREATE TEMPORARY VIEW :name AS :query'),
			array(array(NULL), NULL, 'CREATE VIEW :name AS :query'),
			array(array(FALSE), FALSE, 'CREATE VIEW :name AS :query'),
			array(array(TRUE), TRUE, 'CREATE TEMPORARY VIEW :name AS :query'),
		);
	}

	/**
	 * @covers  SQL\DDL\Create_View::temporary
	 *
	 * @dataProvider    provider_temporary
	 *
	 * @param   array   $arguments  Arguments
	 * @param   boolean $temporary  Expected
	 * @param   string  $value
	 */
	public function test_temporary($arguments, $temporary, $value)
	{
		$view = new Create_View;

		$this->assertSame(
			$view, call_user_func_array(array($view, 'temporary'), $arguments)
		);
		$this->assertSame($temporary, $view->temporary);

		$this->assertSame($value, (string) $view);
		$this->assertSame($this->parameters, $view->parameters);
	}

	/**
	 * @covers  SQL\DDL\Create_View::__toString
	 */
	public function test_toString()
	{
		$view = new Create_View;
		$view
			->replace()
			->temporary()
			->columns(array('a'));

		$this->assertSame(
			'CREATE OR REPLACE TEMPORARY VIEW :name (:columns) AS :query',
			(string) $view
		);
	}
}
