<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.queries
 */
class Database_SQL_DML_Select_Test extends PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), 'SELECT '),
			array(array(array('a')), 'SELECT "a"'),
			array(array(array('a', 'b')), 'SELECT "a", "b"'),
		);
	}

	/**
	 * @covers  SQL_DML_Select::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_constructor($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$class = new ReflectionClass('SQL_DML_Select');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Select::distinct
	 */
	public function test_distinct()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new SQL_DML_Select;

		$this->assertSame($query, $query->distinct(), 'Chainable (void)');
		$this->assertSame('SELECT DISTINCT ', $db->quote($query), 'Distinct (void)');

		$this->assertSame($query, $query->distinct(FALSE), 'Chainable (FALSE)');
		$this->assertSame('SELECT ', $db->quote($query), 'Distinct (FALSE)');

		$this->assertSame($query, $query->distinct(TRUE), 'Chainable (TRUE)');
		$this->assertSame('SELECT DISTINCT ', $db->quote($query), 'Distinct (TRUE)');
	}

	public function provider_column()
	{
		return array(
			array(array(NULL), 'SELECT '),
			array(array(NULL, 'any'), 'SELECT '),

			array(
				array('a'),
				'SELECT "a"',
			),
			array(
				array('a', 'b'),
				'SELECT "a" AS "b"',
			),

			array(
				array(new SQL_Column('a')),
				'SELECT "a"',
			),
			array(
				array(new SQL_Column('a'), 'b'),
				'SELECT "a" AS "b"',
			),

			array(
				array(new SQL_Expression('a')),
				'SELECT a'
			),
			array(
				array(new SQL_Expression('a'), 'b'),
				'SELECT a AS "b"'
			),
		);
	}

	/**
	 * @covers  SQL_DML_Select::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $expected
	 */
	public function test_column($arguments, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Select;

		$result = call_user_func_array(array($statement, 'column'), $arguments);

		$this->assertSame($statement, $result, 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Select::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_column_reset($arguments)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Select;

		call_user_func_array(array($statement, 'column'), $arguments);

		$statement->column(NULL);

		$this->assertSame('SELECT ', $db->quote($statement));
	}

	public function provider_columns()
	{
		return array(
			array(NULL, 'SELECT '),

			array(
				array('a'),
				'SELECT "a"',
			),
			array(
				array('a', 'b'),
				'SELECT "a", "b"',
			),

			array(
				array('a' => 'b'),
				'SELECT "b" AS "a"',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				'SELECT "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Column('a')),
				'SELECT "a"',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'SELECT "a", "b"',
			),

			array(
				array('a' => new SQL_Column('b')),
				'SELECT "b" AS "a"',
			),
			array(
				array('a' => new SQL_Column('b'), 'c' => new SQL_Column('d')),
				'SELECT "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Expression('a')),
				'SELECT a',
			),
			array(
				array(new SQL_Expression('a'), new SQL_Expression('b')),
				'SELECT a, b',
			),

			array(
				array('a' => new SQL_Expression('b')),
				'SELECT b AS "a"',
			),
			array(
				array('a' => new SQL_Expression('b'), 'c' => new SQL_Expression('d')),
				'SELECT b AS "a", d AS "c"',
			),
		);
	}

	/**
	 * @covers  SQL_DML_Select::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_columns($value, $expected)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Select;

		$this->assertSame($statement, $statement->columns($value), 'Chainable');
		$this->assertSame($expected, $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Select::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $value  Argument
	 */
	public function test_columns_reset($value)
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$statement = new SQL_DML_Select;
		$statement->columns($value);

		$statement->columns(NULL);

		$this->assertSame('SELECT ', $db->quote($statement));
	}

	/**
	 * @covers  SQL_DML_Select::from
	 */
	public function test_from()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$query = new SQL_DML_Select(array('one.x'));

		$this->assertSame($query, $query->from('one', 'a'), 'Chainable (table)');
		$this->assertSame('SELECT "pre_one"."x" FROM "pre_one" AS "a"', $db->quote($query));

		$from = new SQL_Table_Reference('one');
		$from->add('two')->join('three');

		$this->assertSame($query, $query->from($from), 'Chainable (from)');
		$this->assertSame('SELECT "pre_one"."x" FROM "pre_one", "pre_two" JOIN "pre_three"', $db->quote($query));
	}

	/**
	 * @covers  SQL_DML_Select::where
	 */
	public function test_where()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new SQL_DML_Select;

		$this->assertSame($query, $query->where(new SQL_Conditions(new SQL_Column('y'), '=', 1)), 'Chainable (conditions)');
		$this->assertSame('SELECT  WHERE "y" = 1', $db->quote($query));

		$this->assertSame($query, $query->where('y', '=', 0), 'Chainable (operands)');
		$this->assertSame('SELECT  WHERE "y" = 0', $db->quote($query));

		$conditions = new SQL_Conditions;
		$conditions->open(NULL)->add(NULL, new SQL_Column('y'), '=', 0)->close();

		$this->assertSame($query, $query->where($conditions, '=', TRUE), 'Chainable (conditions as operand)');
		$this->assertSame('SELECT  WHERE ("y" = 0) = \'1\'', $db->quote($query));
	}

	/**
	 * @covers  SQL_DML_Select::group_by
	 */
	public function test_group_by()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$db->expects($this->any())
			->method('table_prefix')
			->will($this->returnValue('pre_'));

		$query = new SQL_DML_Select(array('x'));

		$this->assertSame($query, $query->group_by(array('y', 'one.z', new SQL_Expression('expr'))));

		$this->assertSame('SELECT "x" GROUP BY "y", "pre_one"."z", expr', $db->quote($query));
	}

	/**
	 * @covers  SQL_DML_Select::having
	 */
	public function test_having()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new SQL_DML_Select(array('x'));

		$this->assertSame($query, $query->having(new SQL_Conditions(new SQL_Column('x'), '=', 1)), 'Chainable (conditions)');
		$this->assertSame('SELECT "x" HAVING "x" = 1', $db->quote($query));

		$this->assertSame($query, $query->having('x', '=', 0), 'Chainable (operands)');
		$this->assertSame('SELECT "x" HAVING "x" = 0', $db->quote($query));

		$conditions = new SQL_Conditions;
		$conditions->open(NULL)->add(NULL, new SQL_Column('x'), '=', 0)->close();

		$this->assertSame($query, $query->having($conditions, '=', TRUE), 'Chainable (conditions as operand)');
		$this->assertSame('SELECT "x" HAVING ("x" = 0) = \'1\'', $db->quote($query));
	}

	/**
	 * @covers  SQL_DML_Select::order_by
	 */
	public function test_order_by()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new SQL_DML_Select(array('x', 'y'));

		$this->assertSame($query, $query->order_by('x'));
		$this->assertSame('SELECT "x", "y" ORDER BY "x"', $db->quote($query));

		$this->assertSame($query, $query->order_by(new SQL_Expression('other'), 'asc'));
		$this->assertSame('SELECT "x", "y" ORDER BY "x", other ASC', $db->quote($query));

		$this->assertSame($query, $query->order_by('y', new SQL_Expression('USING something')));
		$this->assertSame('SELECT "x", "y" ORDER BY "x", other ASC, "y" USING something', $db->quote($query));
	}

	/**
	 * @covers  SQL_DML_Select::limit
	 */
	public function test_limit()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new SQL_DML_Select(array('x'));

		$this->assertSame($query, $query->limit(5));
		$this->assertSame('SELECT "x" LIMIT 5', $db->quote($query));

		$this->assertSame($query, $query->limit(0));
		$this->assertSame('SELECT "x" LIMIT 0', $db->quote($query));
	}

	/**
	 * @covers  SQL_DML_Select::offset
	 */
	public function test_offset()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$query = new SQL_DML_Select(array('x'));

		$this->assertSame($query, $query->offset(5));
		$this->assertSame('SELECT "x" OFFSET 5', $db->quote($query));

		$this->assertSame($query, $query->offset(0));
		$this->assertSame('SELECT "x"', $db->quote($query));
	}

	/**
	 * @covers  SQL_DML_Select::__toString
	 */
	public function test_toString()
	{
		$statement = new SQL_DML_Select;
		$statement
			->distinct()
			->from('a')
			->where('b', '=', 'c')
			->group_by(array('d'))
			->having('e', '=', 'f')
			->order_by('g')
			->limit(1)
			->offset(1);

		$this->assertSame(
			'SELECT DISTINCT :columns FROM :from WHERE :where GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $statement
		);
	}
}
