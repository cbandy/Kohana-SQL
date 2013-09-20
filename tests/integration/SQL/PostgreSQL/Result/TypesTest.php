<?php
namespace SQL\PostgreSQL;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @covers  SQL\PostgreSQL\Result::type
 */
class Result_TypesTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if (empty($_SERVER['POSTGRESQL_NATIVE']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for PostgreSQL');
	}

	/**
	 * @var Connection
	 */
	protected $connection;

	public function setup()
	{
		$config = json_decode($_SERVER['POSTGRESQL_NATIVE'], TRUE);

		$this->connection = new Connection($config);
	}

	public function teardown()
	{
		$this->connection->disconnect();
	}

	public function test_type_returns_the_type()
	{
		$result = $this->connection->execute_query('SELECT 1 AS x, 2.3 AS y');

		$this->assertSame('numeric', $result->type('y'));
		$this->assertSame('int4', $result->type('x'));
	}

	public function test_type_returns_null_when_field_does_not_exist()
	{
		$result = $this->connection->execute_query('SELECT 1');

		$this->assertNull($result->type('nope'));
	}

	public function provider_types()
	{
		return array(
			array('SELECT NULL', 'unknown'),

			array('SELECT 1', 'int4'),
			array('SELECT 1::bigint', 'int8'),
			array('SELECT 1.2', 'numeric'),

			array("SELECT 'a'", 'unknown'),
			array("SELECT 'a'::text", 'text'),
			array("SELECT 'a'::varchar", 'varchar'),

			array('SELECT true', 'bool'),
			array('SELECT CURRENT_DATE', 'date'),
			array('SELECT CURRENT_TIMESTAMP', 'timestamptz'),
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
