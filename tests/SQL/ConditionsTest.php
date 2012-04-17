<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ConditionsTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constuctor()
	{
		return array(
			array(array(), '', array()),

			array(array('a'),           '?', array('a')),
			array(array('b', 'c'),      '? C ?', array('b', NULL)),
			array(array('d', 'e', 'f'), '? E ?', array('d', 'f')),

			array(array(NULL),              '', array()),
			array(array(NULL, 'g'),         '? G ?', array(NULL, NULL)),
			array(array(NULL, 'h', 'i'),    '? H ?', array(NULL, 'i')),
		);
	}

	/**
	 * @covers  SQL\Conditions::__construct
	 *
	 * @dataProvider    provider_constuctor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expression Expected expression
	 * @param   array   $parameters Expected parameters
	 */
	public function test_constructor($arguments, $expression, $parameters)
	{
		$class = new \ReflectionClass('SQL\Conditions');
		$conditions = $class->newInstanceArgs($arguments);

		$this->assertSame($expression, (string) $conditions);
		$this->assertSame($parameters, $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::add
	 * @covers  SQL\Conditions::add_rhs
	 */
	public function test_add()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->add('and', 'a', '=', 0));
		$this->assertSame('? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->add('or', 'b', '<>', 1));
		$this->assertSame('? = ? OR ? <> ?', (string) $conditions);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);

		$this->assertSame($conditions, $conditions->add('and', 'c', '>', 2));
		$this->assertSame('? = ? OR ? <> ? AND ? > ?', (string) $conditions);
		$this->assertSame(
			array('a', 0, 'b', 1, 'c', 2), $conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::add_rhs
	 */
	public function test_add_between()
	{
		$conditions = new Conditions(
			'2009-11-19', 'between', array('2009-11-1', '2009-12-1')
		);

		$this->assertSame('? BETWEEN ? AND ?', (string) $conditions);
		$this->assertSame(
			array('2009-11-19', '2009-11-1', '2009-12-1'),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::add_rhs
	 */
	public function test_add_in()
	{
		$lhs = new Identifier('a');
		$rhs = array('x', 5, new Identifier('z'));

		$conditions = new Conditions($lhs, 'in', $rhs);

		$this->assertSame('? IN (?)', (string) $conditions);
		$this->assertSame(array($lhs, $rhs), $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::close
	 */
	public function test_close()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame(')', (string) $conditions);
		$this->assertSame(array(), $conditions->parameters);

		$this->assertSame($conditions, $conditions->close());
		$this->assertSame('))', (string) $conditions);
		$this->assertSame(array(), $conditions->parameters);
	}

	public function provider_close_empty()
	{
		$result[] = array(new Conditions, '');

		$conditions = new Conditions;
		$conditions->and_open();
		$result[] = array($conditions, '');

		$conditions = new Conditions;
		$conditions->and_open('a', '=', 'b');
		$result[] = array($conditions, '(? = ?)');

		$conditions = new Conditions;
		$conditions->and_not_open();
		$result[] = array($conditions, '');

		$conditions = new Conditions;
		$conditions->and_not_open('a', '=', 'b');
		$result[] = array($conditions, 'NOT (? = ?)');

		$result[] = array(new Conditions('a', '=', 'b'), '? = ?)');

		$conditions = new Conditions('a', '=', 'b');
		$conditions->and_open();
		$result[] = array($conditions, '? = ?');

		$conditions = new Conditions('a', '=', 'b');
		$conditions->and_open('c', '=', 'd');
		$result[] = array($conditions, '? = ? AND (? = ?)');

		$conditions = new Conditions('a', '=', 'b');
		$conditions->and_not_open();
		$result[] = array($conditions, '? = ?');

		$conditions = new Conditions('a', '=', 'b');
		$conditions->and_not_open('c', '=', 'd');
		$result[] = array($conditions, '? = ? AND NOT (? = ?)');

		return $result;
	}

	/**
	 * @covers  SQL\Conditions::close_empty
	 *
	 * @dataProvider    provider_close_empty
	 *
	 * @param   Conditions  $conditions
	 * @param   string      $expected
	 */
	public function test_close_empty($conditions, $expected)
	{
		$before = $conditions->parameters;

		$this->assertSame($conditions, $conditions->close_empty());
		$this->assertSame($expected, (string) $conditions);
		$this->assertSame($before, $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::column
	 */
	public function test_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->column('and', 'a', '=', 0)
		);
		$this->assertSame('? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->column('and', 'b', '<>', 1)
		);
		$this->assertSame('? = ? AND ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->column('or', 'c', 'is', NULL)
		);
		$this->assertSame('? = ? AND ? <> ? OR ? IS ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), 0,
				new Column('b'), 1,
				new Column('c'), NULL,
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::columns
	 */
	public function test_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->columns('and', 'a', '=', 'b')
		);
		$this->assertSame('? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->columns('and', 'c', '<>', 'd')
		);
		$this->assertSame('? = ? AND ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->columns('or', 'e', '=', 'f')
		);
		$this->assertSame('? = ? AND ? <> ? OR ? = ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
				new Column('e'), new Column('f'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::add_unary
	 * @covers  SQL\Conditions::exists
	 */
	public function test_exists()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->exists('and', 'a'));
		$this->assertSame('EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a')),
			$conditions->parameters
		);

		$this->assertSame($conditions, $conditions->exists('and', 'b'));
		$this->assertSame('EXISTS (?) AND EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a'), new Expression('b')),
			$conditions->parameters
		);

		$this->assertSame($conditions, $conditions->exists('or', 'c'));
		$this->assertSame(
			'EXISTS (?) AND EXISTS (?) OR EXISTS (?)', (string) $conditions
		);
		$this->assertEquals(
			array(
				new Expression('a'),
				new Expression('b'),
				new Expression('c'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::not
	 */
	public function test_not()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->not('and', 'a', '=', 0));
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->not('or', 'b', '<>', 1));
		$this->assertSame('NOT ? = ? OR NOT ? <> ?', (string) $conditions);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);

		$this->assertSame($conditions, $conditions->not('and', 'c', '>', 2));
		$this->assertSame(
			'NOT ? = ? OR NOT ? <> ? AND NOT ? > ?', (string) $conditions
		);
		$this->assertSame(
			array('a', 0, 'b', 1, 'c', 2), $conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::not_column
	 */
	public function test_not_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->not_column('and', 'a', '=', 0)
		);
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->not_column('or', 'b', '<>', 1)
		);
		$this->assertSame('NOT ? = ? OR NOT ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->not_column('and', 'c', '>', 2)
		);
		$this->assertSame(
			'NOT ? = ? OR NOT ? <> ? AND NOT ? > ?', (string) $conditions
		);
		$this->assertEquals(
			array(
				new Column('a'), 0,
				new Column('b'), 1,
				new Column('c'), 2,
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::not_columns
	 */
	public function test_not_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->not_columns('and', 'a', '=', 'b')
		);
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->not_columns('or', 'c', '<>', 'd')
		);
		$this->assertSame('NOT ? = ? OR NOT ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->not_columns('and', 'e', '>', 'f')
		);
		$this->assertSame(
			'NOT ? = ? OR NOT ? <> ? AND NOT ? > ?', (string) $conditions
		);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
				new Column('e'), new Column('f'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::add_unary
	 * @covers  SQL\Conditions::not_exists
	 */
	public function test_not_exists()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->not_exists('and', 'a'));
		$this->assertSame('NOT EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a')), $conditions->parameters
		);

		$this->assertSame($conditions, $conditions->not_exists('and', 'b'));
		$this->assertSame(
			'NOT EXISTS (?) AND NOT EXISTS (?)', (string) $conditions
		);
		$this->assertEquals(
			array(new Expression('a'), new Expression('b')),
			$conditions->parameters
		);

		$this->assertSame($conditions, $conditions->not_exists('or', 'c'));
		$this->assertSame(
			'NOT EXISTS (?) AND NOT EXISTS (?) OR NOT EXISTS (?)',
			(string) $conditions
		);
		$this->assertEquals(
			array(
				new Expression('a'),
				new Expression('b'),
				new Expression('c'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::not_open
	 */
	public function test_not_open()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->not_open('and'));
		$this->assertSame('NOT (', (string) $conditions);
		$this->assertSame(array(), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->not_open('or', 'a', '=', 0)
		);
		$this->assertSame('NOT (NOT (? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->not_open('and', 'b', '<>', 1)
		);
		$this->assertSame(
			'NOT (NOT (? = ? AND NOT (? <> ?', (string) $conditions
		);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->not_open('or', 'c', '>', 2)
		);
		$this->assertSame(
			'NOT (NOT (? = ? AND NOT (? <> ? OR NOT (? > ?',
			(string) $conditions
		);
		$this->assertSame(
			array('a', 0, 'b', 1, 'c', 2), $conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::not_open_column
	 */
	public function test_not_open_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->not_open_column('and', 'a', '=', 0)
		);
		$this->assertSame('NOT (? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->not_open_column('or', 'b', '<>', 1)
		);
		$this->assertSame('NOT (? = ? OR NOT (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->not_open_column('and', 'c', '>', 2)
		);
		$this->assertSame(
			'NOT (? = ? OR NOT (? <> ? AND NOT (? > ?', (string) $conditions
		);
		$this->assertEquals(
			array(
				new Column('a'), 0,
				new Column('b'), 1,
				new Column('c'), 2,
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::not_open_columns
	 */
	public function test_not_open_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->not_open_columns('and', 'a', '=', 'b')
		);
		$this->assertSame('NOT (? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->not_open_columns('or', 'c', '<>', 'd')
		);
		$this->assertSame('NOT (? = ? OR NOT (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->not_open_columns('and', 'e', '>', 'f')
		);
		$this->assertSame(
			'NOT (? = ? OR NOT (? <> ? AND NOT (? > ?', (string) $conditions
		);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
				new Column('e'), new Column('f'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::open
	 */
	public function test_open()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->open('and'));
		$this->assertSame('(', (string) $conditions);

		$this->assertSame($conditions, $conditions->open('or', 'a', '=', 0));
		$this->assertSame('((? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->open('and', 'b', '<>', 1));
		$this->assertSame('((? = ? AND (? <> ?', (string) $conditions);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);

		$this->assertSame($conditions, $conditions->open('or', 'c', '>', 2));
		$this->assertSame(
			'((? = ? AND (? <> ? OR (? > ?', (string) $conditions
		);
		$this->assertSame(
			array('a', 0, 'b', 1, 'c', 2), $conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::open_column
	 */
	public function test_open_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->open_column('and', 'a', '=', 0)
		);
		$this->assertSame('(? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->open_column('or', 'b', '<>', 1)
		);
		$this->assertSame('(? = ? OR (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->open_column('and', 'c', '>', 2)
		);
		$this->assertSame('(? = ? OR (? <> ? AND (? > ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), 0,
				new Column('b'), 1,
				new Column('c'), 2,
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::open_columns
	 */
	public function test_open_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->open_columns('and', 'a', '=', 'b')
		);
		$this->assertSame('(? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->open_columns('or', 'c', '<>', 'd')
		);
		$this->assertSame('(? = ? OR (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->open_columns('and', 'e', '>', 'f')
		);
		$this->assertSame('(? = ? OR (? <> ? AND (? > ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
				new Column('e'), new Column('f'),
			),
			$conditions->parameters
		);
	}
}
