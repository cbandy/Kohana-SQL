<?php
namespace SQL\MySQL;

use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @covers  SQL\MySQL\Connection::execute_command
 */
class Connection_CommandTest extends \PHPUnit_Framework_TestCase
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
	}

	public function teardown()
	{
		$this->connection->disconnect();
	}

	public function provider_execute()
	{
		return array(
			array('', 0),
			array(new Statement(''), 0),

			array(
				'INSERT INTO '.$this->table.' (value) VALUES (5)',
				1,
			),
			array(
				new Statement('INSERT INTO '.$this->table.' (value) VALUES (5)'),
				1,
			),

			array(
				'INSERT INTO '.$this->table.' SELECT * FROM '.$this->table.' WHERE 1 <> 1',
				0,
			),
			array(
				new Statement('INSERT INTO '.$this->table.' SELECT * FROM '.$this->table.' WHERE 1 <> 1'),
				0,
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute
	 *
	 * @dataProvider    provider_execute
	 *
	 * @param   string|Statement    $statement  SQL statement
	 * @param   integer             $expected   Expected result
	 */
	public function test_execute($statement, $expected)
	{
		$this->assertSame(
			$expected, $this->connection->execute_command($statement)
		);
	}

	public function provider_execute_parameters()
	{
		return array(
			array(
				new Statement('INSERT INTO '.$this->table.' (value) VALUES (?)', array(5)),
				1,
			),
			array(
				new Statement('INSERT INTO '.$this->table.' SELECT * FROM '.$this->table.' WHERE ? <> ?', array(1,1)),
				0,
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute_parameters
	 *
	 * @dataProvider    provider_execute_parameters
	 *
	 * @param   Statement   $statement  SQL statement
	 * @param   integer     $expected   Expected result
	 */
	public function test_execute_parameters($statement, $expected)
	{
		$this->assertSame(
			$expected, $this->connection->execute_command($statement)
		);
	}

	public function provider_execute_with_query()
	{
		return array(
			array('SELECT * FROM '.$this->table.' WHERE id = 1'),
			array(new Statement('SELECT * FROM '.$this->table.' WHERE id = ?', array(1))),
		);
	}

	/**
	 * @dataProvider    provider_execute_with_query
	 *
	 * @param   string|Statement    $query
	 */
	public function test_execute_with_query_returns_negative_one($query)
	{
		$this->assertSame(-1, $this->connection->execute_command($query));
	}

	/**
	 * @dataProvider    provider_execute_with_query
	 *
	 * @param   string|Statement    $query
	 */
	public function test_execute_with_query_clears_results($query)
	{
		$this->connection->execute_command($query);

		$this->assertSame(1, $this->connection->execute_command(
			'INSERT INTO '.$this->table.' (value) VALUES (1)'
		));
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute
	 * @covers  SQL\MySQL\Connection::handle_error
	 */
	public function test_cannot_execute_with_compound_command()
	{
		$compound = 'INSERT INTO '.$this->table.' (value) VALUES (1);'
			.' INSERT INTO '.$this->table.' (value) VALUES (2), (3)';

		$this->setExpectedException(
			'SQL\RuntimeException', 'SQL syntax',
			$this->mysqli_configured_to_raise_warnings() ? E_WARNING : 1064
		);

		$this->connection->execute_command($compound);
	}

	/**
	 * @covers  SQL\MySQL\Connection::execute_parameters
	 * @covers  SQL\MySQL\Connection::handle_error
	 */
	public function test_cannot_execute_with_compound_arguments()
	{
		$compound = new Statement(
			'INSERT INTO '.$this->table.' (value) VALUES (?);'
			.' INSERT INTO '.$this->table.' (value) VALUES (?), (?)',
			array(1, 2, 3)
		);

		$this->setExpectedException(
			'SQL\RuntimeException', 'SQL syntax',
			$this->mysqli_configured_to_raise_warnings() ? E_WARNING : 1064
		);

		$this->connection->execute_command($compound);
	}
}
