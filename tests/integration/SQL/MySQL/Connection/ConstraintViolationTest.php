<?php
namespace SQL\MySQL;

use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Connection_ConstraintViolationTest extends \PHPUnit_Framework_TestCase
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

	protected $existing_id;
	protected $table = 'kohana_test_table';

	/**
	 * Whether or not the MySQLi extension will raise PHP warnings
	 *
	 * @return  boolean
	 */
	protected function mysqli_configured_to_raise_warnings()
	{
		$driver = new \mysqli_driver;

		return ($driver->report_mode & MYSQLI_REPORT_ERROR)
			&& ! ($driver->report_mode & MYSQLI_REPORT_STRICT);
	}

	public function setup()
	{
		$config = json_decode($_SERVER['MYSQL_NATIVE'], TRUE);

		$this->connection = new Connection($config);
		$this->connection->execute_command(
			'INSERT INTO '.$this->table.' (value) VALUES (1)'
		);

		$this->existing_id = $this->connection->execute_query(
			'SELECT id, value FROM '.$this->table.' WHERE id <> 0'
		)->get();
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute
	 * @covers  SQL\MySQL\Connection::handle_error
	 */
	public function test_execute()
	{
		$this->setExpectedException(
			'SQL\RuntimeException', 'Duplicate entry',
			$this->mysqli_configured_to_raise_warnings() ? E_WARNING : 1062
		);

		$this->connection->execute_command(
			'INSERT INTO '.$this->table.' (id) VALUES ('.$this->existing_id.')'
		);
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute_parameters
	 * @covers  SQL\MySQL\Connection::handle_error
	 */
	public function test_execute_parameters()
	{
		$statement = new Statement(
			'INSERT INTO '.$this->table.' (id) VALUES (?)',
			array($this->existing_id)
		);

		$this->setExpectedException(
			'SQL\RuntimeException', 'Duplicate entry',
			$this->mysqli_configured_to_raise_warnings() ? E_WARNING : 1062
		);

		$this->connection->execute_command($statement);
	}
}
