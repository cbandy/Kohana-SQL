<?php
namespace SQL\MySQL;

use SQL\PDO\Connection;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class DialectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Connection
	 */
	protected $connection;
	protected $table;

	public function setup()
	{
		$config = json_decode($_SERVER['MYSQL'], TRUE);

		$this->connection = new Connection($config);
	}

	/**
	 * The OFFSET of a SELECT can be specified using a comma.
	 *
	 * @link http://dev.mysql.com/doc/en/select.html
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
	 * @link http://dev.mysql.com/doc/en/select.html
	 *
	 * @covers  PDO::query
	 */
	public function test_select_offset_without_limit()
	{
		$this->assertSame(
			array(array('a' => 'a'), array('a' => 'b')),
			$this->connection
				->execute_query("SELECT 'a' UNION SELECT 'b' LIMIT 18446744073709551615 OFFSET 0")
				->to_array()
		);

		$this->assertSame(
			array(array('a' => 'b')),
			$this->connection
				->execute_query("SELECT 'a' UNION SELECT 'b' LIMIT 18446744073709551615 OFFSET 1")
				->to_array()
		);

		$this->setExpectedException('SQL\RuntimeException', 'syntax', '42000');
		$this->connection->execute_query("SELECT 'a' UNION SELECT 'b' OFFSET 1");
	}

	/**
	 * A query with WHERE must also have FROM.
	 *
	 * @link http://dev.mysql.com/doc/en/select.html
	 *
	 * @covers  PDO::query
	 */
	public function test_select_where_without_from()
	{
		$this->assertSame(
			array(array(1 => '1')),
			$this->connection->execute_query('SELECT 1')->to_array()
		);

		$this->assertSame(
			array(array(1 => '1')),
			$this->connection
				->execute_query('SELECT 1 FROM DUAL WHERE 1 = 1')
				->to_array()
		);

		$this->setExpectedException('SQL\RuntimeException', 'syntax', '42000');
		$this->connection->execute_query('SELECT 1 WHERE 1 = 1');
	}

	public function provider_spaceship()
	{
		return array(
			array('1', 'SELECT NULL <=> NULL'),
			array('0', 'SELECT NULL <=> 0'),
			array('0', 'SELECT 0 <=> NULL'),
			array('1', 'SELECT 0 <=> 0'),

			array('0', 'SELECT NOT (NULL <=> NULL)'),
			array('1', 'SELECT NOT (NULL <=> 0)'),
			array('1', 'SELECT NOT (0 <=> NULL)'),
			array('0', 'SELECT NOT (0 <=> 0)'),
		);
	}

	/**
	 * Spaceship (<=>) is a null-safe equality operator.
	 *
	 * @link http://dev.mysql.com/doc/en/comparison-operators.html#operator_equal-to
	 *
	 * @covers  PDO::query
	 *
	 * @dataProvider    provider_spaceship
	 *
	 * @param   string  $expected   Boolean result of the statement
	 * @param   string  $statement
	 */
	public function test_spaceship($expected, $statement)
	{
		$this->assertSame(
			$expected, $this->connection->execute_query($statement)->get()
		);
	}
}
