<?php
namespace SQL\PDO;

use PHPUnit_Extensions_Database_DataSet_DefaultDataSet as DataSet;
use PHPUnit_Extensions_Database_DataSet_DefaultTable as TableData;
use PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData as TableMetaData;
use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
abstract class Connection_Transactions_TestCase extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('pdo'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');
	}

	protected $table = 'kohana_test_table';

	/**
	 * Create a new Connection.
	 *
	 * @return  Connection
	 */
	abstract protected function connection();

	/**
	 * @return  DataSet
	 */
	protected function dataset()
	{
		$values = new TableData(new TableMetaData($this->table, array('value')));
		$values->addRow(array('value' => 50));
		$values->addRow(array('value' => 55));
		$values->addRow(array('value' => 60));

		return new DataSet(array($values));
	}

	/**
	 * @param   PHPUnit_Extensions_Database_DataSet_ITable  $table
	 * @return  array   List of statements
	 */
	protected function populate($table)
	{
		$columns = $table->getTableMetaData()->getColumns();
		$name = $table->getTableMetaData()->getTableName();

		$insert = 'INSERT INTO '.$name.' ('.implode(', ', $columns).')'
			.' VALUES (?'.str_repeat(', ?', count($columns) - 1).')';
		$result = array();

		for ($i = 0; $i < $table->getRowCount(); ++$i)
		{
			$values = array_map(
				function ($column) use ($i, $table)
				{
					return $table->getValue($i, $column);
				},
				$columns
			);

			$result[] = new Statement($insert, $values);
		}

		return $result;
	}

	/**
	 * @param   PHPUnit_Extensions_Database_DataSet_ITable  $table
	 * @return  array   List of statements
	 */
	protected function truncate($table)
	{
		$truncate = new Statement(
			'TRUNCATE TABLE '.$table->getTableMetaData()->getTableName()
		);

		return array($truncate);
	}

	public function setup()
	{
		$connection = $this->connection();

		foreach ($this->dataset() as $table)
		{
			foreach ($this->truncate($table) as $statement)
			{
				$connection->execute_command($statement);
			}

			foreach ($this->populate($table) as $statement)
			{
				$connection->execute_command($statement);
			}
		}
	}

	public function provider_transaction()
	{
		return array(
			array(
				'SELECT * FROM '.$this->table,
				'INSERT INTO '.$this->table.' (value) VALUES (100)'
			),
			array(
				'SELECT * FROM '.$this->table,
				'DELETE FROM '.$this->table.' WHERE value = 60'
			),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::start
	 *
	 * @dataProvider    provider_transaction
	 *
	 * @param   string  $read   SQL query that reads from the dataset
	 * @param   string  $write  SQL command that alters the dataset
	 */
	public function test_start($read, $write)
	{
		$connection = $this->connection();
		$initial = $connection->execute_query($read)->to_array();

		$this->assertNull($connection->start());

		// Changes to the dataset do not affect other connections
		$connection->execute_command($write);
		$this->assertSame(
			$initial, $this->connection()->execute_query($read)->to_array()
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::start
	 */
	public function test_start_twice()
	{
		$connection = $this->connection();
		$connection->start();

		$this->setExpectedException('SQL\RuntimeException', 'active transaction');
		$connection->start();
	}

	/**
	 * @covers  SQL\PDO\Connection::rollback
	 *
	 * @dataProvider    provider_transaction
	 *
	 * @param   string  $read   SQL query that reads from the dataset
	 * @param   string  $write  SQL command that alters the dataset
	 */
	public function test_rollback($read, $write)
	{
		$connection = $this->connection();
		$initial = $connection->execute_query($read)->to_array();

		$connection->start();
		$connection->execute_command($write);

		$this->assertNull($connection->rollback());
		$this->assertSame(
			$initial, $connection->execute_query($read)->to_array()
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::rollback
	 */
	public function test_rollback_no_transaction()
	{
		$connection = $this->connection();

		$this->setExpectedException('SQL\RuntimeException', 'active transaction');
		$connection->rollback();
	}

	/**
	 * @covers  SQL\PDO\Connection::commit
	 *
	 * @dataProvider    provider_transaction
	 *
	 * @param   string  $read   SQL query that reads from the dataset
	 * @param   string  $write  SQL command that alters the dataset
	 */
	public function test_commit($read, $write)
	{
		$connection = $this->connection();
		$connection->start();
		$connection->execute_command($write);
		$changed = $connection->execute_query($read)->to_array();

		$this->assertNull($connection->commit());
		$this->assertSame(
			$changed, $this->connection()->execute_query($read)->to_array()
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::commit
	 */
	public function test_commit_no_transaction()
	{
		$connection = $this->connection();

		$this->setExpectedException('SQL\RuntimeException', 'active transaction');
		$connection->commit();
	}
}
