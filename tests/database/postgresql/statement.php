<?php

require_once dirname(__FILE__).'/testcase'.EXT;
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Statement_Test extends Database_PostgreSQL_TestCase
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

	public function provider_constructor_name()
	{
		return array
		(
			array('a'),
			array('b'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Statement::__construct
	 * @covers  Database_PostgreSQL_Statement::__toString
	 * @dataProvider    provider_constructor_name
	 *
	 * @param   string  $value  Statement name
	 */
	public function test_constructor_statement($value)
	{
		$db = Database::factory();
		$statement = new Database_PostgreSQL_Statement($db, $value);

		$this->assertSame($value, (string) $statement);
	}

	public function provider_constructor_parameters()
	{
		return array
		(
			array(array('a')),
			array(array('b' => 'c')),
			array(array('d', 'e' => 'f')),
			array(array('g' => 'h', 'i')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Statement::__construct
	 * @dataProvider    provider_constructor_parameters
	 *
	 * @param   string  $value  Statement parameters
	 */
	public function test_constructor_parameters($value)
	{
		$db = Database::factory();
		$statement = new Database_PostgreSQL_Statement($db, 'name', $value);

		$this->assertSame($value, $statement->parameters());
	}

	/**
	 * @covers  Database_PostgreSQL_Statement::__construct
	 */
	public function test_constructor_parameters_bound()
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, 'SELECT $1::integer');

		$var;
		$parameters[] =& $var;

		$statement = new Database_PostgreSQL_Statement($db, $name, $parameters);

		$var = 1;
		$this->assertSame($parameters, $statement->parameters());
		$this->assertEquals($var, $statement->execute_query()->get());
	}

	/**
	 * @covers  Database_PostgreSQL_Statement::deallocate
	 */
	public function test_deallocate()
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, 'SELECT 1');
		$statement = new Database_PostgreSQL_Statement($db, $name);

		$this->assertNull($statement->deallocate());

		try
		{
			$statement->deallocate();
			$this->fail('Calling deallocate() twice should fail with a Database_Exception');
		}
		catch (Database_Exception $e) {}
	}

	public function provider_execute_command()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array
		(
			array(1, 'INSERT INTO '.$table.' VALUES (10)', array()),
			array(3, 'DELETE FROM '.$table.' WHERE "value" = $1', array(65)),
			array(2, 'UPDATE '.$table.' SET "value" = $1 WHERE "value" = $2', array(20, 60)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Statement::execute_command
	 * @dataProvider    provider_execute_command
	 *
	 * @param   integer $expected   Expected result
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 */
	public function test_execute_command($expected, $statement, $parameters)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, $statement);
		$statement = new Database_PostgreSQL_Statement($db, $name, $parameters);

		$this->assertSame($expected, $statement->execute_command());
	}

	public function provider_execute_insert()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array
		(
			array(array(2, 8), 'INSERT INTO '.$table.' ("value") VALUES (10), (20) RETURNING "id"', array()),
			array(array(1, 8), 'INSERT INTO '.$table.' ("value") VALUES ($1) RETURNING "id"', array(50)),
			array(array(2, 8), 'INSERT INTO '.$table.' ("value") VALUES ($1), ($2) RETURNING "id"', array(70, 80)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Statement::execute_insert
	 * @dataProvider    provider_execute_insert
	 *
	 * @param   array  $expected   Expected result
	 * @param   string $statement  SQL statement
	 * @param   array  $parameters Statement parameters
	 */
	public function test_execute_insert($expected, $statement, $parameters)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, $statement);
		$statement = new Database_PostgreSQL_Statement($db, $name, $parameters);

		$this->assertEquals($expected, $statement->execute_insert('id'));
	}

	public function provider_execute_query()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		return array
		(
			array('SELECT * FROM '.$table.' WHERE value < 60', array(), array(
				array('id' => 1, 'value' => 50),
				array('id' => 2, 'value' => 55),
			)),
			array('SELECT * FROM '.$table.' WHERE value < $1', array(60), array(
				array('id' => 1, 'value' => 50),
				array('id' => 2, 'value' => 55),
			)),
			array('SELECT * FROM '.$table.' WHERE value > $1', array(60), array(
				array('id' => 5, 'value' => 65),
				array('id' => 6, 'value' => 65),
				array('id' => 7, 'value' => 65),
			)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Statement::execute_query
	 * @dataProvider    provider_execute_query
	 *
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Statement parameters
	 * @param   array   $expected   Expected result
	 */
	public function test_execute_query($statement, $parameters, $expected)
	{
		$db = Database::factory();
		$name = $db->prepare(NULL, $statement);
		$statement = new Database_PostgreSQL_Statement($db, $name, $parameters);

		$result = $statement->execute_query();

		$this->assertInstanceOf('Database_PostgreSQL_Result', $result);
		$this->assertEquals($expected, $result->as_array());
	}
}
