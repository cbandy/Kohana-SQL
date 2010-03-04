<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.select
 */
class Database_Select_Test extends PHPUnit_Framework_TestCase
{
	public function test_select()
	{
		$db = new Database_Select_Test_DB;
		$query = new Database_Query_Select;

		$this->assertSame($query, $query->select(array('x', 'y' => new Database_Expression('count(*)'))));
		$this->assertSame('SELECT "x", count(*) AS "y"', $db->quote($query));

		$this->assertSame($query, $query->select(new Database_Expression('arbitrary')));
		$this->assertSame('SELECT arbitrary', $db->quote($query));
	}

	public function test_column()
	{
		$db = new Database_Select_Test_DB;
		$query = new Database_Query_Select;

		$this->assertSame($query, $query->column('one.x', 'a'));
		$this->assertSame('SELECT "pre_one"."x" AS "a"', $db->quote($query));

		$this->assertSame($query, $query->column('y'));
		$this->assertSame('SELECT "pre_one"."x" AS "a", "y"', $db->quote($query));
	}

	public function test_from()
	{
		$db = new Database_Select_Test_DB;
		$query = new Database_Query_Select(array('one.x'));

		$from = new Database_Query_From('one');
		$from->add('two')->join('three');

		$this->assertSame($query, $query->from($from));
		$this->assertSame('SELECT "pre_one"."x" FROM "pre_one", "pre_two" JOIN "pre_three"', $db->quote($query));
	}

	public function test_where()
	{
		$db = new Database_Select_Test_DB;
		$query = new Database_Query_Select(new Database_Expression(1));

		$this->assertSame($query, $query->where(new Database_Query_Conditions(new Database_Column('y'), '=', 1)));

		$this->assertSame('SELECT 1 WHERE "y" = 1', $db->quote($query));
	}

	public function test_group_by()
	{
		$db = new Database_Select_Test_DB;
		$query = new Database_Query_Select(array('x'));

		$this->assertSame($query, $query->group_by(array('y', 'one.z', new Database_Expression('expr'))));

		$this->assertSame('SELECT "x" GROUP BY "y", "pre_one"."z", expr', $db->quote($query));
	}

	public function test_having()
	{
		$db = new Database_Select_Test_DB;
		$query = new Database_Query_Select(array('x'));

		$this->assertSame($query, $query->having(new Database_Query_Conditions(new Database_Column('x'), '=', 1)));

		$this->assertSame('SELECT "x" HAVING "x" = 1', $db->quote($query));
	}

	public function test_order_by()
	{
		$db = new Database_Select_Test_DB;
		$query = new Database_Query_Select(array('x', 'y'));

		$this->assertSame($query, $query->order_by('x'));
		$this->assertSame('SELECT "x", "y" ORDER BY "x"', $db->quote($query));

		$this->assertSame($query, $query->order_by(new Database_Expression('other'), 'asc'));
		$this->assertSame('SELECT "x", "y" ORDER BY "x", other ASC', $db->quote($query));

		$this->assertSame($query, $query->order_by('y', new Database_Expression('USING something')));
		$this->assertSame('SELECT "x", "y" ORDER BY "x", other ASC, "y" USING something', $db->quote($query));
	}
}

class Database_Select_Test_DB extends Database
{
	public function escape($value)
	{
		return "'$value'";
	}

	public function table_prefix()
	{
		return 'pre_';
	}
}