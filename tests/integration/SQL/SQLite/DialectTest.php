<?php
namespace SQL\SQLite;

use SQL\PDO\Connection;

/**
 * @package     SQL
 * @subpackage  SQLite
 * @author      Chris Bandy
 */
class DialectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Connection
	 */
	protected $connection;
	protected $table = 'kohana_test_table';

	public function setup()
	{
		$config = json_decode($_SERVER['SQLITE'], TRUE);

		$this->connection = new Connection($config);
	}

	public function provider_is_and_is_not()
	{
		return array(
			array('1', 'SELECT NULL IS NULL'),
			array('0', 'SELECT NULL IS 0'),
			array('0', 'SELECT 0 IS NULL'),
			array('0', 'SELECT NULL IS NOT NULL'),
			array('1', 'SELECT NULL IS NOT 0'),
			array('1', 'SELECT 0 IS NOT NULL'),
		);
	}

	/**
	 * IS and IS NOT are null-safe equality operators.
	 *
	 * @link http://www.sqlite.org/lang_expr.html#binaryops
	 *
	 * @covers  PDO::query
	 *
	 * @dataProvider    provider_is_and_is_not
	 *
	 * @param   string  $expected   Boolean result of the statement
	 * @param   string  $statement
	 */
	public function test_is_and_is_not($expected, $statement)
	{
		$this->assertSame(
			$expected, $this->connection->execute_query($statement)->get()
		);
	}

	/**
	 * The OFFSET of a SELECT can be specified using a comma.
	 *
	 * @link http://www.sqlite.org/lang_select.html#orderby
	 *
	 * @covers  PDO::query
	 */
	public function test_select_offset_alternate()
	{
		$verbose = $this->connection
			->execute_query("SELECT 'a' UNION SELECT 'b' LIMIT 5 OFFSET 1")
			->to_array();

		$alternate = $this->connection
			->execute_query("SELECT 'a' UNION SELECT 'b' LIMIT 1,5")
			->to_array();

		$this->assertSame($verbose, $alternate);
	}

	/**
	 * SELECT cannot have an OFFSET without a LIMIT.
	 *
	 * @link http://www.sqlite.org/lang_select.html#orderby
	 *
	 * @covers  PDO::query
	 */
	public function test_select_offset_without_limit()
	{
		$this->assertSame(
			array(array("'a'" => 'a'), array("'a'" => 'b')),
			$this->connection
				->execute_query("SELECT 'a' UNION SELECT 'b' LIMIT 9223372036854775807 OFFSET 0")
				->to_array()
		);

		$this->assertSame(
			array(array("'a'" => 'b')),
			$this->connection
				->execute_query("SELECT 'a' UNION SELECT 'b' LIMIT 9223372036854775807 OFFSET 1")
				->to_array()
		);

		$this->setExpectedException('SQL\RuntimeException', 'syntax error', 'HY000');
		$this->connection->execute_query("SELECT 'a' UNION SELECT 'b' OFFSET 1");
	}

	/**
	 * A query with a limit is only allowed as a subquery in a compound SELECT.
	 *
	 * @link http://www.sqlite.org/lang_select.html
	 *
	 * @covers  PDO::query
	 */
	public function test_union_limit()
	{
		$this->assertSame(
			array(),
			$this->connection->execute_query("SELECT 'a' LIMIT 0")->to_array()
		);

		$this->assertSame(
			array(array("'a'" => 'b')),
			$this->connection
				->execute_query("SELECT * FROM (SELECT 'a' LIMIT 0) UNION SELECT 'b'")
				->to_array()
		);

		$this->setExpectedException(
			'SQL\RuntimeException', 'LIMIT clause should come after UNION', 'HY000'
		);
		$this->connection->execute_query("SELECT 'a' LIMIT 0 UNION SELECT 'b'");
	}

	/**
	 * A sorted query is only allowed as a subquery in a compound SELECT.
	 *
	 * @link http://www.sqlite.org/lang_select.html
	 *
	 * @covers  PDO::query
	 */
	public function test_union_order_by()
	{
		$this->assertSame(
			array(array("'a'" => 'a')),
			$this->connection
				->execute_query("SELECT 'a' ORDER BY 'a'")
				->to_array()
		);

		$this->assertSame(
			array(array("'a'" => 'a'), array("'a'" => 'b')),
			$this->connection
				->execute_query("SELECT * FROM (SELECT 'a' ORDER BY 'a') UNION SELECT 'b'")
				->to_array()
		);

		$this->setExpectedException(
			'SQL\RuntimeException', 'ORDER BY clause should come after UNION', 'HY000'
		);
		$this->connection->execute_query("SELECT 'a' ORDER BY 'a' UNION SELECT 'b'");
	}

	public function provider_union_parentheses()
	{
		return array(
			array("SELECT 'a' UNION (SELECT 'b')"),
			array("(SELECT 'a') UNION SELECT 'b'"),
			array("(SELECT 'a') UNION (SELECT 'b')"),
		);
	}

	/**
	 * Queries in a compound SELECT cannot be wrapped in parentheses.
	 *
	 * @link http://www.sqlite.org/lang_select.html#compound
	 *
	 * @covers  PDO::query
	 *
	 * @dataProvider    provider_union_parentheses
	 *
	 * @param   string  $statement
	 */
	public function test_union_parentheses($statement)
	{
		$this->setExpectedException('SQL\RuntimeException', 'syntax error', 'HY000');
		$this->connection->execute_query($statement);
	}
}
