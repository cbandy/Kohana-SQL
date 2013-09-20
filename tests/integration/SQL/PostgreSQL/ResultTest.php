<?php
namespace SQL\PostgreSQL;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class ResultTest extends \PHPUnit_Framework_TestCase
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

	/**
	 * @var Result
	 */
	protected $result;

	public function setup()
	{
		$config = json_decode($_SERVER['POSTGRESQL_NATIVE'], TRUE);

		$this->connection = new Connection($config);
		$this->result = $this->connection->execute_query(
			'SELECT 1 AS value UNION SELECT 2 UNION SELECT 3'
		);
	}

	public function teardown()
	{
		$this->connection->disconnect();
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::__construct
	 */
	public function test_count()
	{
		$this->assertCount(
			2, $this->connection->execute_query('SELECT 1 UNION SELECT 2')
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::current
	 */
	public function test_current_returns_an_associative_array()
	{
		$this->assertSame(array('value' => '1'), $this->result->current());
	}

	/**
	 * @coversNothing
	 */
	public function test_current_does_not_move_the_pointer()
	{
		$before = $this->result->key();
		$this->result->current();

		$this->assertSame($before, $this->result->key());
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::fetch
	 */
	public function test_fetch_returns_an_associative_array()
	{
		$this->assertSame(array('value' => '1'), $this->result->fetch(0));
		$this->assertSame(array('value' => '3'), $this->result->fetch(2));
	}

	/**
	 * @coversNothing
	 */
	public function test_fetch_does_not_move_the_pointer()
	{
		$before = $this->result->key();
		$this->result->fetch(1);

		$this->assertSame($before, $this->result->key());
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::fetch
	 */
	public function test_fetch_raises_a_warning_when_position_is_negative()
	{
		if (error_reporting() & E_WARNING)
		{
			$this->setExpectedException(
				'PHPUnit_Framework_Error_Warning', 'must be greater'
			);
		}

		$this->assertFalse($this->result->fetch('-1'));
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::fetch
	 */
	public function test_fetch_raises_a_warning_when_position_is_too_high()
	{
		if (error_reporting() & E_WARNING)
		{
			$this->setExpectedException(
				'PHPUnit_Framework_Error_Warning', 'Unable to jump to row'
			);
		}

		$this->assertFalse($this->result->fetch('99'));
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::get
	 */
	public function test_get_returns_the_field()
	{
		$result = $this->connection->execute_query('SELECT 1 AS a, 2 AS b');

		$this->assertSame('2', $result->get('b'));
		$this->assertSame('1', $result->get('a'));
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::get
	 */
	public function test_get_returns_the_default_when_invalid()
	{
		$default = new \stdClass;
		$this->result->seek(2)->next();

		$this->assertSame($default, $this->result->get(NULL, $default));
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::get
	 */
	public function test_get_returns_the_default_when_field_does_not_exist()
	{
		$default = new \stdClass;

		$this->assertSame($default, $this->result->get('nope', $default));
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::get
	 */
	public function test_get_returns_the_default_when_value_is_null()
	{
		$default = new \stdClass;
		$result = $this->connection->execute_query('SELECT NULL AS value');

		$this->assertSame($default, $result->get('value', $default));
	}

	/**
	 * @coversNothing
	 */
	public function test_get_does_not_move_the_pointer()
	{
		$before = $this->result->key();
		$this->result->get();

		$this->assertSame($before, $this->result->key());
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::offsetGet
	 */
	public function test_array_access_returns_associative_array()
	{
		$this->assertSame(array('value' => '2'), $this->result[1]);
		$this->assertSame(array('value' => '1'), $this->result[0]);
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::offsetGet
	 */
	public function test_array_access_returns_null_when_invalid()
	{
		$this->assertNull($this->result[-1]);
		$this->assertNull($this->result[99]);
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::to_array
	 */
	public function test_to_array_returns_indexed_associative_arrays()
	{
		$this->assertSame(
			array(
				array('value' => '1'),
				array('value' => '2'),
				array('value' => '3'),
			),
			$this->result->to_array()
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::to_array
	 */
	public function test_to_array_returns_associative_arrays()
	{
		$this->assertSame(
			array(
				'1' => array('value' => '1'),
				'2' => array('value' => '2'),
				'3' => array('value' => '3'),
			),
			$this->result->to_array('value')
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::to_array
	 */
	public function test_to_array_returns_indexed_field_values()
	{
		$this->assertSame(
			array('1', '2', '3'),
			$this->result->to_array(NULL, 'value')
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::to_array
	 */
	public function test_to_array_returns_associative_field_values()
	{
		$this->assertSame(
			array('1' => '1', '2' => '2', '3' => '3'),
			$this->result->to_array('value', 'value')
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Result::to_array
	 */
	public function test_to_array_returns_empty_array_for_empty_set()
	{
		$result = $this->connection->execute_query('SELECT 1 WHERE 1 <> 1');

		$this->assertSame(array(), $result->to_array());
		$this->assertSame(array(), $result->to_array('1'));
		$this->assertSame(array(), $result->to_array(NULL, '1'));
		$this->assertSame(array(), $result->to_array('1', '1'));
	}
}
