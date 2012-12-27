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

	protected $table = 'kohana_test_table';

	public function setup()
	{
		$config = json_decode($_SERVER['MYSQL'], TRUE);

		$this->connection = new Connection($config);
	}

	public function provider_create_table_column_definition()
	{
		return array(
			array('c1 integer AUTO_INCREMENT KEY NOT NULL'),
			array('c2 integer AUTO_INCREMENT NOT NULL KEY'),
			array('c3 integer KEY NOT NULL AUTO_INCREMENT'),

			array('c4 integer DEFAULT 0 NOT NULL UNIQUE'),
			array('c5 integer NOT NULL DEFAULT 0 UNIQUE'),
			array('c6 integer UNIQUE DEFAULT 0 NOT NULL'),

			array("c7 integer COMMENT 'text' DEFAULT 0 UNIQUE"),
			array("c8 integer DEFAULT 0 COMMENT 'text' UNIQUE"),
			array("c9 integer UNIQUE COMMENT 'some' DEFAULT 0"),
		);
	}

	/**
	 * The order of column definition elements is less strict than described in
	 * the documentation.
	 *
	 * @link http://dev.mysql.com/doc/en/create-table.html
	 *
	 * @covers  PDO::exec
	 *
	 * @dataProvider    provider_create_table_column_definition
	 *
	 * @param   string  $definition SQL fragment defining a table schema
	 */
	public function test_create_table_column_definition($definition)
	{
		$result = $this->connection->execute_command(
			'CREATE TEMPORARY TABLE kohana_schema_t1 ('.$definition.')'
		);

		$this->assertSame(0, $result);
	}

	public function provider_create_table_column_reference()
	{
		return array(
			array('AUTO_INCREMENT KEY'),
			array("COMMENT 'text'"),
			array('NOT NULL'),
			array('UNIQUE'),
		);
	}

	/**
	 * A foreign key constraint must be the last element in a column definition.
	 *
	 * @link http://dev.mysql.com/doc/en/create-table.html
	 *
	 * @covers  PDO::exec
	 *
	 * @dataProvider    provider_create_table_column_reference
	 *
	 * @param   string  $other  SQL fragment of a column definition
	 */
	public function test_create_table_column_reference($other)
	{
		$this->assertSame(
			0,
			$this->connection->execute_command(
				'CREATE TEMPORARY TABLE kohana_schema_t1 ('
				.'c1 integer '.$other.' REFERENCES '.$this->table.' (id)'
				.')'
			)
		);

		$this->setExpectedException('SQL\RuntimeException', 'syntax', '42000');
		$this->connection->execute_command(
			'CREATE TEMPORARY TABLE kohana_schema_t2 ('
			.'c2 integer REFERENCES '.$this->table.' (id) '.$other
			.')'
		);
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
