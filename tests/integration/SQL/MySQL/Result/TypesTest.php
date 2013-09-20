<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @covers  SQL\MySQL\Result::type
 */
class Result_TypesTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('mysqli'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('MySQLi extension not installed');

		if (empty($_SERVER['MYSQL_NATIVE']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for MySQL');
	}

	/**
	 * @var Connection
	 */
	protected $connection;

	public function setup()
	{
		$config = json_decode($_SERVER['MYSQL_NATIVE'], TRUE);

		$this->connection = new Connection($config);
	}

	public function teardown()
	{
		$this->connection->disconnect();
	}

	/**
	 * @covers  SQL\MySQL\Result::fetch_fields
	 */
	public function test_type_returns_the_type()
	{
		$result = $this->connection->execute_query('SELECT 1 AS x, 2.3 AS y');

		$this->assertSame('numeric', $result->type('y'));
		$this->assertSame('bigint', $result->type('x'));
	}

	public function test_type_returns_null_when_field_does_not_exist()
	{
		$result = $this->connection->execute_query('SELECT 1');

		$this->assertNull($result->type('nope'));
	}

	public function provider_types()
	{
		return array(
			array('SELECT NULL', 'null'),

			array('SELECT 1', 'bigint'),
			array('SELECT 1.2', 'numeric'),
			array("SELECT 'a'", 'varchar'),

			array('SELECT true', 'bigint'),
			array('SELECT CURRENT_DATE', 'date'),
			array('SELECT CURRENT_TIMESTAMP', 'datetime'),
		);
	}

	/**
	 * @dataProvider    provider_types
	 */
	public function test_types($query, $expected)
	{
		$result = $this->connection->execute_query($query);

		$this->assertSame($expected, $result->type());
	}
}
