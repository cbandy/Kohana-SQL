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
}
