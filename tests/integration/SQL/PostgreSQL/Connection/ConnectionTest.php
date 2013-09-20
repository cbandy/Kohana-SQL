<?php
namespace SQL\PostgreSQL;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class Connection_ConnectionTest extends \PHPUnit_Framework_TestCase
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

	/**
	 * @covers  SQL\PostgreSQL\Connection::connect
	 */
	public function test_connect()
	{
		$this->assertNull($this->connection->connect());
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::connect
	 */
	public function test_connect_raises_exception()
	{
		$connection = new Connection(array('port' => -1));

		$this->setExpectedException(
			'SQL\RuntimeException', 'Unable to connect', E_WARNING
		);

		$connection->connect();
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::disconnect
	 */
	public function test_disconnect_when_disconnected()
	{
		$this->assertNull($this->connection->disconnect());
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::disconnect
	 */
	public function test_disconnect_when_connected()
	{
		$this->connection->connect();

		$this->assertNull($this->connection->disconnect());
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::connect
	 * @covers  SQL\PostgreSQL\Connection::disconnect
	 */
	public function test_reconnect()
	{
		$this->connection->connect();
		$this->connection->disconnect();

		$this->assertNull($this->connection->connect());
	}
}
