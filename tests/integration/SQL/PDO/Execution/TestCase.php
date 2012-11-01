<?php
namespace SQL\PDO;

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
abstract class Execution_TestCase extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('pdo'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');
	}

	/**
	 * @var PDO
	 */
	protected $connection;

	/**
	 * 1-indexed parameters should not be passed to PDOStatement::execute(),
	 * though some drivers allow it.
	 *
	 * @covers  PDOStatement::execute
	 *
	 * @link    http://bugs.php.net/37290
	 */
	public function test_execute_with_one_indexed_parameters()
	{
		// PostgreSQL: Addition coerces the value to integer
		$statement = 'SELECT ? + 0';

		// 0-indexed array parameters succeed
		$this->assertTrue(
			$this->connection->prepare($statement)->execute(array(1))
		);

		$result = $this->connection->prepare($statement);

		try
		{
			$result = $result->execute(array(1 => 1));
		}
		catch (\PDOException $e)
		{
			// The exception message and code vary between drivers
			switch ($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME))
			{
				case 'mysql':
					$this->setExpectedException(
						'PDOException', 'parameter number', 'HY093'
					);
				break;
				case 'sqlite':
					$this->setExpectedException(
						'PDOException', 'index out of range', 'HY000'
					);
				break;
				case 'sqlsrv':
					$this->setExpectedException(
						'PDOException', 'field incorrect', '07002'
					);
				break;
			}

			throw $e;
		}

		// Some drivers allow 1-indexed array parameters
		switch ($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$this->assertTrue($result);
			break;
			default:
				$this->assertFalse($result);
		}
	}

	/**
	 * Named parameters should not be used more than once, though most drivers
	 * allow it.
	 *
	 * @covers  PDOStatement::execute
	 *
	 * @link    http://bugs.php.net/33886
	 * @link    http://www.php.net/manual/pdo.prepare.php
	 */
	public function test_execute_with_multiple_named_parameters()
	{
		// PostgreSQL: Addition coerces the values to integer
		$statement = 'SELECT :a + 0, :a + 0';

		$result = $this->connection->prepare($statement);

		try
		{
			$this->assertTrue($result->execute(array(':a' => 1)));
		}
		catch (\PDOException $e)
		{
			switch ($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME))
			{
				case 'sqlsrv':
					$this->setExpectedException(
						'PDOException', 'field incorrect', '07002'
					);
				break;
			}

			throw $e;
		}

		$result = $result->fetch(\PDO::FETCH_NUM);

		// The returned data types vary between drivers
		switch ($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME))
		{
			case 'pgsql':
				$this->assertSame(array(1,1), $result);
			break;
			default:
				$this->assertSame(array('1','1'), $result);
		}
	}
}
