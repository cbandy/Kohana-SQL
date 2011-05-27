<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  PDO
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Transactions_Test extends Database_PDO_TestCase
{
	protected $_table = 'kohana_test_table';

	protected function getDataSet()
	{
		$dataset = new PHPUnit_Extensions_Database_DataSet_CsvDataSet;
		$dataset->addTable(
			Database::factory()->table_prefix().$this->_table,
			dirname(dirname(__FILE__)).'/datasets/values.csv'
		);

		return $dataset;
	}

	public function provider_command()
	{
		return array(
			array(
				new SQL_Expression('SELECT * FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('INSERT INTO ? (value) VALUES (100)', array(new SQL_Table($this->_table))),
			),
			array(
				new SQL_Expression('SELECT * FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('DELETE FROM ? WHERE value = 60', array(new SQL_Table($this->_table))),
			),
		);
	}

	/**
	 * @covers  Database_PDO::begin
	 *
	 * @dataProvider    provider_command
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 */
	public function test_begin($query, $command)
	{
		$connection = $this->getConnection()->getConnection();

		if ($connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlsrv')
		{
			// This test hangs with the default isolation level, READ COMMITTED.
			// It succeeds with READ_COMMITTED_SNAPSHOT ON.
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		// Start a transaction
		$this->assertNull($db->begin());
		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'No change');

		// Change the dataset
		$db->execute_command($command);
		$this->assertSame($initial, Database::factory()->execute_query($query)->as_array(), 'Other connection unaffected');
	}

	/**
	 * @covers  Database_PDO::rollback
	 *
	 * @dataProvider    provider_command
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 */
	public function test_rollback($query, $command)
	{
		$db = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$db->begin();

		// Change the dataset
		$db->execute_command($command);

		$this->assertNull($db->rollback());
		$this->assertSame($initial, $db->execute_query($query)->as_array(), 'Changes reverted');
	}

	/**
	 * Trying to rollback when not in a transaction throws an exception.
	 *
	 * @covers  Database_PDO::rollback
	 */
	public function test_rollback_no_transaction()
	{
		$db = Database::factory();

		$this->setExpectedException(
			'Database_Exception', 'no active transaction'
		);

		$db->rollback();
	}

	public function provider_result()
	{
		return array(
			array(
				new SQL_Expression('SELECT value FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('INSERT INTO ? (value) VALUES (100)', array(new SQL_Table($this->_table))),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 60),
					array('value' => 60),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
					array('value' => 100),
				),
			),
			array(
				new SQL_Expression('SELECT value FROM ?', array(new SQL_Table($this->_table))),
				new SQL_Expression('DELETE FROM ? WHERE value = 60', array(new SQL_Table($this->_table))),
				array(
					array('value' => 50),
					array('value' => 55),
					array('value' => 65),
					array('value' => 65),
					array('value' => 65),
				),
			),
		);
	}

	/**
	 * @covers  Database_PDO::commit
	 *
	 * @dataProvider    provider_result
	 *
	 * @param   SQL_Expression  $query      SQL query that reads from the dataset
	 * @param   SQL_Expression  $command    SQL command that alters the dataset
	 * @param   array           $expected   Expected result of the query after command is executed and after commit
	 */
	public function test_commit($query, $command, $expected)
	{
		$connection = $this->getConnection()->getConnection();

		if ($connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlsrv')
		{
			// This test hangs with the default isolation level, READ COMMITTED.
			// It succeeds with READ_COMMITTED_SNAPSHOT ON.
			$this->markTestSkipped();
		}

		$db = Database::factory();
		$other = Database::factory();
		$initial = $db->execute_query($query)->as_array();

		$db->begin();

		// Change the dataset
		$db->execute_command($command);
		$this->assertSame($initial, $other->execute_query($query)->as_array(), 'Other connection unaffected');

		$this->assertNull($db->commit());
		$this->assertEquals($expected, $other->execute_query($query)->as_array(), 'Other connection affected');
	}
}
