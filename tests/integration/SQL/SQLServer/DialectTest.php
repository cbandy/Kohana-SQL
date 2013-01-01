<?php
namespace SQL\SQLServer;

use SQL\PDO\Connection;
use SQL\Compiler;

/**
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 */
class DialectTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if (empty($_SERVER['SQLSERVER']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for SQL Server');
	}

	/**
	 * @var Connection
	 */
	protected $connection;

	protected $table = 'kohana_test_table';

	/**
	 * @var string  Version of SQL Server
	 */
	protected $version;

	protected function array_table_data($table, $columns, $data)
	{
		$compiler = new Compiler;
		$parenthesize_and_quote = function ($row) use ($compiler)
		{
			return '('.implode(', ',
				array_map(array($compiler, 'quote_literal'), $row)
			).')';
		};

		$this->connection->execute_command('TRUNCATE TABLE '.$table);
		$this->connection->execute_command(
			'INSERT INTO '.$table.' ('.implode(', ', $columns).') VALUES '
			.implode(', ', array_map($parenthesize_and_quote, $data))
		);
	}

	public function setup()
	{
		$config = json_decode($_SERVER['SQLSERVER'], TRUE);

		$this->connection = new Connection($config);
		$this->version = $this->connection
			->execute_query("SELECT SERVERPROPERTY('productversion')")
			->get();
	}

	/**
	 * A common table expression cannot be used inside a subquery.
	 *
	 * @covers  PDO::query
	 */
	public function test_cte_invalid_as_subquery()
	{
		$this->array_table_data($this->table, array('value'), array(
			array(40), array(20)
		));

		$statement = 'SELECT * FROM '.$this->table.' WHERE VALUE IN (%s)';
		$subquery = 'SELECT value FROM '.$this->table;
		$query = 'SELECT TOP 1 * FROM ('.$subquery.') AS qry';
		$cte_query = 'WITH cte AS ('.$subquery.') SELECT TOP 1 * FROM cte';

		$this->assertEquals(
			array(array('id' => 1, 'value' => 40)),
			$this->connection->execute_query(sprintf($statement, $query))
				->to_array()
		);

		$this->setExpectedException('SQL\RuntimeException', 'Syntax', '42000');
		$this->connection->execute_query(sprintf($statement, $cte_query));
	}

	/**
	 * SELECT..OFFSET is available in SQL Server 2012 and later.
	 *
	 * @covers  PDO::query
	 */
	public function test_offset()
	{
		$this->array_table_data($this->table, array('value'), array(
			array(40), array(30), array(20), array(10)
		));

		$statement = 'SELECT value FROM '.$this->table
			.' ORDER BY id OFFSET 1 ROW';

		if (version_compare($this->version, '11.0', '<'))
		{
			$this->setExpectedException(
				'SQL\RuntimeException', 'Syntax', '42000'
			);
		}

		$this->assertEquals(
			array(
				array('value' => 30),
				array('value' => 20),
				array('value' => 10),
			),
			$this->connection->execute_query($statement)->to_array()
		);
	}

	/**
	 * SELECT..OFFSET can be emulated using a common table expression and window
	 * function.
	 *
	 * @covers  PDO::query
	 */
	public function test_offset_emulation_cte()
	{
		$this->array_table_data($this->table, array('value'), array(
			array(40), array(30), array(20), array(10)
		));

		$statement = 'WITH cte AS ('
			.'SELECT value, ROW_NUMBER() OVER(ORDER BY id) AS num'
			.' FROM '.$this->table
			.') SELECT * FROM cte WHERE num > 1';

		$this->assertEquals(
			array(
				array('value' => 30, 'num' => 2),
				array('value' => 20, 'num' => 3),
				array('value' => 10, 'num' => 4),
			),
			$this->connection->execute_query($statement)->to_array()
		);

		$statement = 'WITH cte AS ('
			.'SELECT value, ROW_NUMBER() OVER(ORDER BY id) AS num'
			.' FROM '.$this->table
			.') SELECT TOP 2 * FROM cte WHERE num > 1';

		$this->assertEquals(
			array(
				array('value' => 30, 'num' => 2),
				array('value' => 20, 'num' => 3),
			),
			$this->connection->execute_query($statement)->to_array()
		);
	}

	/**
	 * SELECT..OFFSET can be emulated using a subquery and window function.
	 *
	 * @covers  PDO::query
	 */
	public function test_offset_emulation_subquery()
	{
		$this->array_table_data($this->table, array('value'), array(
			array(40), array(30), array(20), array(10)
		));

		$statement = 'SELECT * FROM ('
			.'SELECT value, ROW_NUMBER() OVER(ORDER BY id) AS num'
			.' FROM '.$this->table
			.') AS qry WHERE qry.num > 1';

		$this->assertEquals(
			array(
				array('value' => 30, 'num' => 2),
				array('value' => 20, 'num' => 3),
				array('value' => 10, 'num' => 4),
			),
			$this->connection->execute_query($statement)->to_array()
		);

		$statement = 'SELECT TOP 2 * FROM ('
			.'SELECT value, ROW_NUMBER() OVER(ORDER BY id) AS num'
			.' FROM '.$this->table
			.') AS qry WHERE qry.num > 1';

		$this->assertEquals(
			array(
				array('value' => 30, 'num' => 2),
				array('value' => 20, 'num' => 3),
			),
			$this->connection->execute_query($statement)->to_array()
		);
	}
}
