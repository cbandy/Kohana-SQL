<?php
namespace SQL\PostgreSQL;

use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @covers  SQL\PostgreSQL\Connection::evaluate_query
 * @covers  SQL\PostgreSQL\Connection::execute_query
 */
class Connection_QueryTest extends \PHPUnit_Framework_TestCase
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
				'SELECT 1 AS value UNION SELECT 2',
				array(array('value' => '1'), array('value' => '2')),
			),
			array(
				new Statement('SELECT 1 AS value UNION SELECT 2'),
				array(array('value' => '1'), array('value' => '2')),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::execute
	 *
	 * @dataProvider    provider_execute
	 *
	 * @param   string|Statement    $statement  SQL statement
	 * @param   array               $expected   Expected result set
	 */
	public function test_execute($statement, $expected)
	{
		$result = $this->connection->execute_query($statement);

		$this->assertInstanceOf('SQL\PostgreSQL\Result', $result);
		$this->assertSame($expected, $result->to_array());
	}

	/**
	 * @covers  SQL\PostgreSQL\Connection::execute_parameters
	 */
	public function test_execute_parameters()
	{
		$result = $this->connection->execute_query(
			new Statement('SELECT $1 AS value UNION SELECT $2', array(3, 4))
		);

		$this->assertInstanceOf('SQL\PostgreSQL\Result', $result);
		$this->assertSame(
			array(array('value' => '3'), array('value' => '4')),
			$result->to_array()
		);
	}

	public function provider_execute_blank()
	{
		return array(
			array(''),
			array(new Statement('')),
		);
	}

	/**
	 * @dataProvider    provider_execute_blank
	 *
	 * @param   string|Statement    $statement  SQL statement
	 */
	public function test_execute_blank_returns_null($statement)
	{
		$this->assertNull($this->connection->execute_query($statement));
	}

	public function test_execute_with_command_returns_null()
	{
		$this->assertNull(
			$this->connection->execute_query('DELETE FROM '.$this->table)
		);
	}

	public function test_execute_with_copy_does_not_hang()
	{
		$this->assertNull(
			$this->connection->execute_query('COPY '.$this->table.' TO STDOUT')
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

		$this->connection->execute_query('kohana-invalid-sql');
	}
}
