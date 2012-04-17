<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Conditions_HelpersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\Conditions::and_column
	 */
	public function test_and_column()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->and_column('a', '=', 0));
		$this->assertSame('? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->and_column('b', '<>', 1));
		$this->assertSame('? = ? AND ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_columns
	 */
	public function test_and_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->and_columns('a', '=', 'b')
		);
		$this->assertSame('? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->and_columns('c', '<>', 'd')
		);
		$this->assertSame('? = ? AND ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_exists
	 */
	public function test_and_exists()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->and_exists('a'));
		$this->assertSame('EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a')), $conditions->parameters
		);

		$this->assertSame($conditions, $conditions->and_exists('b'));
		$this->assertSame('EXISTS (?) AND EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a'), new Expression('b')),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_not
	 */
	public function test_and_not()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->and_not('a', '=', 0));
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->and_not('b', '<>', 1));
		$this->assertSame('NOT ? = ? AND NOT ? <> ?', (string) $conditions);
		$this->assertSame(
			array('a', 0, 'b', 1), $conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_not_column
	 */
	public function test_and_not_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->and_not_column('a', '=', 0)
		);
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->and_not_column('b', '<>', 1)
		);
		$this->assertSame('NOT ? = ? AND NOT ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_not_columns
	 */
	public function test_and_not_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->and_not_columns('a', '=', 'b')
		);
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->and_not_columns('c', '<>', 'd')
		);
		$this->assertSame('NOT ? = ? AND NOT ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_not_exists
	 */
	public function test_and_not_exists()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->and_not_exists('a'));
		$this->assertSame('NOT EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a')), $conditions->parameters
		);

		$this->assertSame($conditions, $conditions->and_not_exists('b'));
		$this->assertSame(
			'NOT EXISTS (?) AND NOT EXISTS (?)', (string) $conditions
		);
		$this->assertEquals(
			array(new Expression('a'), new Expression('b')),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_not_open
	 */
	public function test_and_not_open()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->and_not_open());
		$this->assertSame('NOT (', (string) $conditions);
		$this->assertSame(array(), $conditions->parameters);

		$this->assertSame($conditions, $conditions->and_not_open('a', '=', 0));
		$this->assertSame('NOT (NOT (? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->and_not_open('b', '<>', 1));
		$this->assertSame(
			'NOT (NOT (? = ? AND NOT (? <> ?', (string) $conditions
		);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::and_not_open_column
	 */
	public function test_and_not_open_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->and_not_open_column('a', '=', 0)
		);
		$this->assertSame('NOT (? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->and_not_open_column('b', '<>', 1)
		);
		$this->assertSame('NOT (? = ? AND NOT (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_not_open_columns
	 */
	public function test_and_not_open_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->and_not_open_columns('a', '=', 'b')
		);
		$this->assertSame('NOT (? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->and_not_open_columns('c', '<>', 'd')
		);
		$this->assertSame('NOT (? = ? AND NOT (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_open
	 */
	public function test_and_open()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->and_open());
		$this->assertSame('(', (string) $conditions);
		$this->assertSame(array(), $conditions->parameters);

		$this->assertSame($conditions, $conditions->and_open('a', '=', 0));
		$this->assertSame('((? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->and_open('b', '<>', 1));
		$this->assertSame('((? = ? AND (? <> ?', (string) $conditions);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::and_open_column
	 */
	public function test_and_open_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->and_open_column('a', '=', 0)
		);
		$this->assertSame('(? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->and_open_column('b', '<>', 1)
		);
		$this->assertSame('(? = ? AND (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::and_open_columns
	 */
	public function test_and_open_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->and_open_columns('a', '=', 'b')
		);
		$this->assertSame('(? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->and_open_columns('c', '<>', 'd')
		);
		$this->assertSame('(? = ? AND (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_column
	 */
	public function test_or_column()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_column('a', '=', 0));
		$this->assertSame('? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->or_column('b', '<>', 1));
		$this->assertSame('? = ? OR ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_columns
	 */
	public function test_or_columns()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_columns('a', '=', 'b'));
		$this->assertSame('? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame($conditions, $conditions->or_columns('c', '<>', 'd'));
		$this->assertSame('? = ? OR ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_exists
	 */
	public function test_or_exists()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_exists('a'));
		$this->assertSame('EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a')), $conditions->parameters
		);

		$this->assertSame($conditions, $conditions->or_exists('b'));
		$this->assertSame('EXISTS (?) OR EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a'), new Expression('b')),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_not
	 */
	public function test_or_not()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_not('a', '=', 0));
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->or_not('b', '<>', 1));
		$this->assertSame('NOT ? = ? OR NOT ? <> ?', (string) $conditions);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::or_not_column
	 */
	public function test_or_not_column()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_not_column('a', '=', 0));
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->or_not_column('b', '<>', 1)
		);
		$this->assertSame('NOT ? = ? OR NOT ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_not_columns
	 */
	public function test_or_not_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->or_not_columns('a', '=', 'b')
		);
		$this->assertSame('NOT ? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->or_not_columns('c', '<>', 'd')
		);
		$this->assertSame('NOT ? = ? OR NOT ? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_not_exists
	 */
	public function test_or_not_exists()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_not_exists('a'));
		$this->assertSame('NOT EXISTS (?)', (string) $conditions);
		$this->assertEquals(
			array(new Expression('a')), $conditions->parameters
		);

		$this->assertSame($conditions, $conditions->or_not_exists('b'));
		$this->assertSame(
			'NOT EXISTS (?) OR NOT EXISTS (?)', (string) $conditions
		);
		$this->assertEquals(
			array(new Expression('a'), new Expression('b')),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_not_open
	 */
	public function test_or_not_open()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_not_open());
		$this->assertSame('NOT (', (string) $conditions);
		$this->assertSame(array(), $conditions->parameters);

		$this->assertSame($conditions, $conditions->or_not_open('a', '=', 0));
		$this->assertSame('NOT (NOT (? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->or_not_open('b', '<>', 1));
		$this->assertSame(
			'NOT (NOT (? = ? OR NOT (? <> ?', (string) $conditions
		);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::or_not_open_column
	 */
	public function test_or_not_open_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->or_not_open_column('a', '=', 0)
		);
		$this->assertSame('NOT (? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->or_not_open_column('b', '<>', 1)
		);
		$this->assertSame('NOT (? = ? OR NOT (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_not_open_columns
	 */
	public function test_or_not_open_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->or_not_open_columns('a', '=', 'b')
		);
		$this->assertSame('NOT (? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->or_not_open_columns('c', '<>', 'd')
		);
		$this->assertSame('NOT (? = ? OR NOT (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_open
	 */
	public function test_or_open()
	{
		$conditions = new Conditions;

		$this->assertSame($conditions, $conditions->or_open());
		$this->assertSame('(', (string) $conditions);
		$this->assertSame(array(), $conditions->parameters);

		$this->assertSame($conditions, $conditions->or_open('a', '=', 0));
		$this->assertSame('((? = ?', (string) $conditions);
		$this->assertSame(array('a', 0), $conditions->parameters);

		$this->assertSame($conditions, $conditions->or_open('b', '<>', 1));
		$this->assertSame('((? = ? OR (? <> ?', (string) $conditions);
		$this->assertSame(array('a', 0, 'b', 1), $conditions->parameters);
	}

	/**
	 * @covers  SQL\Conditions::or_open_column
	 */
	public function test_or_open_column()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->or_open_column('a', '=', 0)
		);
		$this->assertSame('(? = ?', (string) $conditions);
		$this->assertEquals(array(new Column('a'), 0), $conditions->parameters);

		$this->assertSame(
			$conditions, $conditions->or_open_column('b', '<>', 1)
		);
		$this->assertSame('(? = ? OR (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), 0, new Column('b'), 1),
			$conditions->parameters
		);
	}

	/**
	 * @covers  SQL\Conditions::or_open_columns
	 */
	public function test_or_open_columns()
	{
		$conditions = new Conditions;

		$this->assertSame(
			$conditions, $conditions->or_open_columns('a', '=', 'b')
		);
		$this->assertSame('(? = ?', (string) $conditions);
		$this->assertEquals(
			array(new Column('a'), new Column('b')), $conditions->parameters
		);

		$this->assertSame(
			$conditions, $conditions->or_open_columns('c', '<>', 'd')
		);
		$this->assertSame('(? = ? OR (? <> ?', (string) $conditions);
		$this->assertEquals(
			array(
				new Column('a'), new Column('b'),
				new Column('c'), new Column('d'),
			),
			$conditions->parameters
		);
	}
}
