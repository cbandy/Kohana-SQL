<?php
namespace SQL\PostgreSQL;

use SQL\PDO\Connection;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class DialectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Connection
	 */
	protected $connection;

	public function setup()
	{
		$config = json_decode($_SERVER['POSTGRESQL'], TRUE);

		$this->connection = new Connection($config);
	}

	public function provider_is_distinct_from()
	{
		return array(
			array(FALSE, 'SELECT NULL IS DISTINCT FROM NULL'),
			array(TRUE,  'SELECT NULL IS DISTINCT FROM 0'),
			array(TRUE,  'SELECT 0 IS DISTINCT FROM NULL'),
			array(FALSE, 'SELECT 0 IS DISTINCT FROM 0'),

			array(TRUE,  'SELECT NULL IS NOT DISTINCT FROM NULL'),
			array(FALSE, 'SELECT NULL IS NOT DISTINCT FROM 0'),
			array(FALSE, 'SELECT 0 IS NOT DISTINCT FROM NULL'),
			array(TRUE,  'SELECT 0 IS NOT DISTINCT FROM 0'),
		);
	}

	/**
	 * IS DISTINCT FROM and IS NOT DISTINCT FROM are null-safe equality
	 * operators.
	 *
	 * @link http://www.postgresql.org/docs/current/static/functions-comparison.html
	 *
	 * @covers  PDO::query
	 *
	 * @dataProvider    provider_is_distinct_from
	 *
	 * @param   boolean $expected   Result of the statement
	 * @param   string  $statement
	 */
	public function test_is_distinct_from($expected, $statement)
	{
		$this->assertSame(
			$expected, $this->connection->execute_query($statement)->get()
		);
	}
}
