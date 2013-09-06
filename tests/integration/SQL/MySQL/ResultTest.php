<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Connection_ResultTest extends \PHPUnit_Framework_TestCase
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

	protected function empty_set()
	{
		return $this->connection->execute_query('SELECT 1 FROM DUAL WHERE 1 <> 1');
	}

	public function setup()
	{
		$config = json_decode($_SERVER['MYSQL_NATIVE'], TRUE);

		$this->connection = new Connection($config);
	}

	/**
	 * @covers  SQL\MySQL\Result::__construct
	 * @covers  SQL\MySQL\Result::valid
	 */
	public function test_empty_set_is_invalid()
	{
		$this->assertFalse($this->empty_set()->valid());
	}

	/**
	 * @covers  SQL\MySQL\Result::current
	 * @covers  SQL\MySQL\Result::fetch
	 */
	public function test_current_returns_null_when_invalid()
	{
		$this->assertNull($this->empty_set()->current());
	}

	/**
	 * @covers  SQL\MySQL\Result::current
	 * @covers  SQL\MySQL\Result::fetch
	 */
	public function test_current_returns_associative_array()
	{
		$result = $this->connection->execute_query('SELECT 1 AS value');

		$this->assertSame(array('value' => '1'), $result->current());
	}

	/**
	 * @covers  SQL\MySQL\Result::current
	 */
	public function test_current_does_not_move_the_pointer()
	{
		$result = $this->connection->execute_query('SELECT 1 UNION SELECT 2');
		$before = $result->key();
		$result->current();

		$this->assertSame($before, $result->key());
	}

	/**
	 * @covers  SQL\MySQL\Result::next
	 */
	public function test_next_is_chainable()
	{
		$result = $this->connection->execute_query('SELECT 1 UNION SELECT 2');

		$this->assertSame($result, $result->next());
	}

	/**
	 * @covers  SQL\MySQL\Result::next
	 */
	public function test_next_moves_the_pointer()
	{
		$result = $this->connection->execute_query('SELECT 1 UNION SELECT 2');
		$before = $result->key();
		$result->next();

		$this->assertNotEquals($before, $result->key());
	}

	/**
	 * @covers  SQL\MySQL\Result::__destruct
	 */
	public function test_destructor_frees_the_result()
	{
		$mysqli_result = $this->getMockBuilder('mysqli_result')
			->disableOriginalConstructor()
			->getMock();

		$mysqli_result->expects($this->atLeastOnce())->method('free');

		$this->result = new Result($mysqli_result);
		$this->result->__destruct();
	}
}
