<?php
namespace SQL\PDO;

use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
abstract class Connection_TestCase extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('pdo'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');
	}

	/**
	 * @var Connection
	 */
	protected $connection;
	protected $table = 'kohana_test_table';

	public function provider_execute_command()
	{
		return array(
			array('INSERT INTO '.$this->table.' (value) VALUES (5)', 1),
			array(
				new Statement(
					'INSERT INTO '.$this->table.' (value) VALUES (5)'
				),
				1,
			),
			array(
				new Statement(
					'INSERT INTO '.$this->table.' (value) VALUES (?)', array(1)
				),
				1,
			),

			array(
				'INSERT INTO '.$this->table
				.' SELECT * FROM '.$this->table.' WHERE 1 <> 1',
				0,
			),
			array(
				new Statement(
					'INSERT INTO '.$this->table
					.' SELECT * FROM '.$this->table.' WHERE 1 <> 1'
				),
				0,
			),
			array(
				new Statement(
					'INSERT INTO '.$this->table
					.' SELECT * FROM '.$this->table.' WHERE ? <> ?',
					array(1, 1)
				),
				0,
			),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::execute_command
	 *
	 * @dataProvider    provider_execute_command
	 *
	 * @param   string|Statement    $statement  SQL statement
	 * @param   integer             $expected   Expected result
	 */
	public function test_execute_command($statement, $expected)
	{
		$this->assertSame(
			$expected, $this->connection->execute_command($statement)
		);
	}

	public function provider_execute_command_empty()
	{
		return array(
			array(''),
			array(new Statement('')),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::execute_command
	 *
	 * @dataProvider  provider_execute_command_empty
	 *
	 * @param   string|Statement    $value  Empty statement
	 */
	public function test_execute_command_empty($value)
	{
		$this->assertSame(0, $this->connection->execute_command($value));
	}

	public function provider_execute_command_error()
	{
		return array(
			array('kohana invalid command'),
			array(new Statement('kohana invalid command')),
			array(new Statement('kohana ? invalid command', array(1))),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::execute_command
	 *
	 * @dataProvider  provider_execute_command_error
	 *
	 * @param   string|Statement    $value  Bad SQL statement
	 */
	public function test_execute_command_error($value)
	{
		$this->setExpectedException('SQL\RuntimeException', 'syntax', 'HY000');

		$this->connection->execute_command($value);
	}

	public function provider_execute_query()
	{
		return array(
			array('SELECT 1 AS value', array(
				array('value' => 1),
			)),
			array(new Statement('SELECT 1 AS value'), array(
				array('value' => 1),
			)),

			// PostgreSQL: addition operator implies integer type
			array(new Statement('SELECT ? + 0 AS value', array(2)), array(
				array('value' => 2),
			)),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::execute_query
	 *
	 * @dataProvider    provider_execute_query
	 *
	 * @param   string|Statement    $statement  SQL statement
	 * @param   array               $expected   Expected result
	 */
	public function test_execute_query($statement, $expected)
	{
		$result = $this->connection->execute_query($statement);

		$this->assertInstanceOf('SQL\PDO\Result_Seekable', $result);
		$this->assertEquals($expected, $result->to_array());
	}

	/**
	 * @covers  SQL\PDO\Connection::execute_query
	 */
	public function test_execute_query_command()
	{
		$this->assertNull(
			$this->connection->execute_query('DELETE FROM '.$this->table)
		);
	}

	public function provider_execute_query_empty()
	{
		return array(
			array(''),
			array(new Statement('')),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::execute_query
	 *
	 * @dataProvider  provider_execute_query_empty
	 *
	 * @param   string|Statement    $value  Empty statement
	 */
	public function test_execute_query_empty($value)
	{
		$this->assertNull($this->connection->execute_query($value));
	}

	public function provider_execute_query_error()
	{
		return array(
			array('kohana invalid query'),
			array(new Statement('kohana invalid query')),
			array(new Statement('kohana ? invalid query', array(1))),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::execute_query
	 *
	 * @dataProvider  provider_execute_query_error
	 *
	 * @param   string|Statement    $value  Bad SQL statement
	 */
	public function test_execute_query_error($value)
	{
		$this->setExpectedException('SQL\RuntimeException', 'syntax', 'HY000');

		$this->connection->execute_query($value);
	}
}
