<?php
namespace SQL\PostgreSQL;

use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @covers  SQL\PostgreSQL\Connection::evaluate_command
 * @covers  SQL\PostgreSQL\Connection::execute_command
 */
class Connection_CommandTest extends \PHPUnit_Framework_TestCase
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

	protected $table = 'kohana_test_table';

	public function setup()
	{
		$config = json_decode($_SERVER['POSTGRESQL_NATIVE'], TRUE);

		$this->connection = new Connection($config);
	}

	public function provider_execute()
	{
		return array(
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
	 * @covers  SQL\PostgreSQL\Connection::execute
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
				new Statement('INSERT INTO '.$this->table.' (value) VALUES ($1)', array(5)),
				1,
			),
			array(
				new Statement('INSERT INTO '.$this->table.' SELECT * FROM '.$this->table.' WHERE $1 <> $1', array(1)),
				0,
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::execute_parameters
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

	public function test_execute_with_query_returns_number_of_result_rows()
	{
		$query = 'SELECT 1 UNION SELECT 2 UNION SELECT 3';

		$this->assertSame(3, $this->connection->execute_command($query));
	}

	public function test_execute_with_compound_command_returns_last_affected_rows()
	{
		$compound = 'INSERT INTO '.$this->table.' (value) VALUES (1);'
			.' INSERT INTO '.$this->table.' (value) VALUES (2), (3)';

		$this->assertSame(2, $this->connection->execute_command($compound));
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::execute_parameters
	 */
	public function test_cannot_execute_with_compound_arguments()
	{
		$compound = new Statement(
			'INSERT INTO '.$this->table.' (value) VALUES ($1);'
			.' INSERT INTO '.$this->table.' (value) VALUES ($2), ($3)',
			array(1, 2, 3)
		);

		$this->setExpectedException(
			'SQL\RuntimeException', 'multiple commands', E_WARNING
		);

		$this->connection->execute_command($compound);
	}

	public function test_execute_with_copy_does_not_hang()
	{
		$this->assertSame(
			0, $this->connection->execute_command('COPY '.$this->table.' TO STDOUT')
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::execute
	 * @covers  SQL\PostgreSQL\Connection::handle_error
	 */
	public function test_execute_raises_exception()
	{
		$this->setExpectedException(
			'SQL\RuntimeException', 'syntax error', E_WARNING
		);

		$this->connection->execute_command('kohana-invalid-sql');
	}
}
