<?php
namespace SQL\MySQL;

use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @covers  SQL\MySQL\Connection::execute_query
 */
class Connection_QueryTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('mysqli'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('MySQLi extension not installed');

		if ( ! method_exists('mysqli_stmt', 'get_result'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('MySQLi extension not using mysqlnd');

		if (empty($_SERVER['MYSQL_NATIVE']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for MySQL');
	}

	/**
	 * @var Connection
	 */
	protected $connection;

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
		$this->connection->execute_command('TRUNCATE '.$this->table);
		$this->connection->execute_command(
			'INSERT INTO '.$this->table.' (value) VALUES (1), (2)'
		);
	}

	public function teardown()
	{
		$this->connection->disconnect();
	}

	public function provider_execute()
	{
		return array(
			array(
				'SELECT value FROM '.$this->table.' WHERE id <> 0',
				array(array('value' => '1'), array('value' => '2')),
			),
			array(
				new Statement('SELECT value FROM '.$this->table.' WHERE id <> 0'),
				array(array('value' => '1'), array('value' => '2')),
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute
	 *
	 * @dataProvider    provider_execute
	 *
	 * @param   string|Statement    $statement  SQL statement
	 * @param   array               $expected   Expected result set
	 */
	public function test_execute($statement, $expected)
	{
		$result = $this->connection->execute_query($statement);

		$this->assertInstanceOf('SQL\MySQL\Result', $result);
		$this->assertSame($expected, $result->to_array());
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute_parameters
	 */
	public function test_execute_parameters()
	{
		$result = $this->connection->execute_query(
			new Statement('SELECT value FROM '.$this->table.' WHERE id <> ?', array(0))
		);

		$this->assertInstanceOf('SQL\MySQL\Result', $result);
		$this->assertSame(
			array(array('value' => 1), array('value' => 2)),
			$result->to_array()
		);
	}

	public function provider_execute_with_command()
	{
		return array(
			array('DELETE FROM '.$this->table.' WHERE id = 1'),
			array(new Statement('DELETE FROM '.$this->table.' WHERE id = ?', array(1))),
		);
	}

	/**
	 * @dataProvider    provider_execute_with_command
	 *
	 * @param   string|Statement    $command
	 */
	public function test_execute_with_command_returns_null($command)
	{
		$this->assertNull($this->connection->execute_query($command));
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute
	 * @covers  SQL\MySQL\Connection::handle_error
	 */
	public function test_cannot_execute_with_multiple_queries()
	{
		$multiple = 'SELECT * FROM '.$this->table.' WHERE value = 1;'
			.' SELECT * FROM '.$this->table.' WHERE value = 2';

		$this->setExpectedException(
			'SQL\RuntimeException', 'SQL syntax',
			$this->mysqli_configured_to_raise_warnings() ? E_WARNING : 1064
		);

		$this->connection->execute_query($multiple);
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute_parameters
	 * @covers  SQL\MySQL\Connection::handle_error
	 */
	public function test_cannot_execute_with_multiple_parameterized_queries()
	{
		$multiple = new Statement(
			'SELECT * FROM '.$this->table.' WHERE value = ?;'
			.' SELECT * FROM '.$this->table.' WHERE value = ?',
			array(1, 2)
		);

		$this->setExpectedException(
			'SQL\RuntimeException', 'SQL syntax',
			$this->mysqli_configured_to_raise_warnings() ? E_WARNING : 1064
		);

		$this->connection->execute_query($multiple);
	}
}
