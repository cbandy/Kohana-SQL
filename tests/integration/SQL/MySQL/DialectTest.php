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
