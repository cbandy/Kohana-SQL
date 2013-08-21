<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Connection_ConnectionTest extends \PHPUnit_Framework_TestCase
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

	/**
	 * @covers  SQL\MySQL\Connection::connect
	 */
	public function test_connect()
	{
		$this->assertNull($this->connection->connect());
	}

	/**
	 * @covers  SQL\MySQL\Connection::connect
	 * @covers  SQL\MySQL\Connection::handle_error
	 */
	public function test_connect_raises_exception()
	{
		$connection = new Connection(array(
			'hostname' => '127.0.0.1', 'port' => 1023
		));
		$driver = new \mysqli_driver;

		$this->setExpectedException(
			'SQL\RuntimeException', "Can't connect",
			($driver->report_mode & MYSQLI_REPORT_STRICT) ? 2003 : E_WARNING
		);

		$connection->connect();
	}

	/**
	 * @covers  SQL\MySQL\Connection::disconnect
	 */
	public function test_disconnect_when_disconnected()
	{
		$this->assertNull($this->connection->disconnect());
	}

	/**
	 * @covers  SQL\MySQL\Connection::disconnect
	 */
	public function test_disconnect_when_connected()
	{
		$this->connection->connect();

		$this->assertNull($this->connection->disconnect());
	}

	/**
	 * @covers  SQL\MySQL\Connection::connect
	 * @covers  SQL\MySQL\Connection::disconnect
	 */
	public function test_reconnect()
	{
		$this->connection->connect();
		$this->connection->disconnect();

		$this->assertNull($this->connection->connect());
	}
}
