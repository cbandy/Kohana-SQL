<?php

/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Database_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	protected $_table = 'kohana_test_table';

	public function provider_alter_table()
	{
		return array(
			array(array(), new Database_PostgreSQL_DDL_Alter_Table()),
			array(array('a'), new Database_PostgreSQL_DDL_Alter_Table('a')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::alter_table
	 *
	 * @dataProvider    provider_alter_table
	 *
	 * @param   array                           $arguments
	 * @param   Database_PostgreSQL_DDL_Alter_Table $expected
	 */
	public function test_alter_table($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::alter_table', $arguments);
		$this->assertEquals($expected, $statement);
	}

	/**
	 * @covers  Database_PostgreSQL::charset
	 */
	public function test_charset()
	{
		$db = Database::factory();

		$this->assertNull($db->charset('utf8'));
	}

	/**
	 * @covers  Database_PostgreSQL::charset
	 */
	public function test_charset_invalid()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'invalid value');

		$db->charset('kohana-invalid-encoding');
	}

	public function provider_configuration()
	{
		return array(
			array(array(), ''),
			array(array('connection' => array()), ''),

			array(array('connection' => array('hostname' => 'a')), "host='a'"),
			array(array('connection' => array('port' => 1)), " port='1'"),
			array(array('connection' => array('username' => 'b')), " user='b'"),
			array(array('connection' => array('password' => 'c')), " password='c'"),
			array(array('connection' => array('password' => "q'c")), " password='q\\'c'"),
			array(array('connection' => array('database' => 'd')), " dbname='d'"),

			array(
				array('connection' => array('options' => '--x=y')),
				" options='--x=y'",
			),
			array(
				array('connection' => array('options' => "--z='m n'")),
				" options='--z=\'m n\''",
			),

			array(
				array('connection' => array('ssl' => FALSE)),
				" sslmode='disable'",
			),
			array(
				array('connection' => array('ssl' => TRUE)),
				" sslmode='require'",
			),
			array(
				array('connection' => array('ssl' => 's')),
				" sslmode='s'",
			),

			array(
				array(
					'connection' => array(
						'hostname' => 'j',
						'port' => 2,
						'username' => 'k',
						'password' => 'l',
						'database' => 'm',
						'options' => '--n=o',
						'ssl' => 'p',
					),
				),
				"host='j' port='2' user='k' password='l' dbname='m' options='--n=o' sslmode='p'",
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::configuration
	 *
	 * @dataProvider    provider_configuration
	 *
	 * @param   array   $argument
	 * @param   string  $expected   Connection string
	 */
	public function test_configuration($argument, $expected)
	{
		$this->assertSame(
			$expected, Database_PostgreSQL::configuration($argument)
		);
	}

	/**
	 * Throws an exception when the table does not exist.
	 *
	 * @covers  Database_PostgreSQL::copy_from
	 */
	public function test_copy_from_error()
	{
		$db = Database::factory();

		$this->setExpectedException(
			'Database_Exception',
			'does not exist',
			error_reporting() & E_WARNING
		);

		$db->copy_from('kohana-nonexistent-table', array("8\t70"));
	}

	/**
	 * Throws an exception when the table does not exist.
	 *
	 * @covers  Database_PostgreSQL::copy_to
	 */
	public function test_copy_to_error()
	{
		$db = Database::factory();

		$this->setExpectedException(
			'Database_Exception',
			'does not exist',
			error_reporting() & E_WARNING
		);

		$db->copy_to('kohana-nonexistent-table');
	}

	public function provider_create_index()
	{
		return array(
			array(array(), new Database_PostgreSQL_DDL_Create_Index),
			array(array('a'), new Database_PostgreSQL_DDL_Create_Index('a')),
			array(array('a', 'b'), new Database_PostgreSQL_DDL_Create_Index('a', 'b')),
			array(array('a', 'b', array('c')), new Database_PostgreSQL_DDL_Create_Index('a', 'b', array('c'))),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::create_index
	 *
	 * @dataProvider    provider_create_index
	 *
	 * @param   array                               $arguments
	 * @param   Database_PostgreSQL_DDL_Create_Index    $expected
	 */
	public function test_create_index($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::create_index', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_datatype()
	{
		return array
		(
			array('money', 'exact', TRUE),
			array('bytea', NULL, array('type' => 'binary')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::datatype
	 * @dataProvider provider_datatype
	 */
	public function test_datatype($type, $attribute, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->datatype($type, $attribute));
	}

	public function provider_ddl_column()
	{
		return array(
			array(array(), new Database_PostgreSQL_DDL_Column),
			array(array('a'), new Database_PostgreSQL_DDL_Column('a')),
			array(
				array('a', 'b'),
				new Database_PostgreSQL_DDL_Column('a', 'b')
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::ddl_column
	 *
	 * @dataProvider    provider_ddl_column
	 *
	 * @param   array                           $arguments
	 * @param   Database_PostgreSQL_DDL_Column  $expected
	 */
	public function test_ddl_column($arguments, $expected)
	{
		$column = call_user_func_array('Database_PostgreSQL::ddl_column', $arguments);
		$this->assertEquals($expected, $column);
	}

	public function provider_delete()
	{
		return array(
			array(array(), new Database_PostgreSQL_DML_Delete),
			array(array('a'), new Database_PostgreSQL_DML_Delete('a')),
			array(array('a', 'b'), new Database_PostgreSQL_DML_Delete('a', 'b')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::delete
	 *
	 * @dataProvider    provider_delete
	 *
	 * @param   array                       $arguments
	 * @param   Database_PostgreSQL_DML_Delete  $expected
	 */
	public function test_delete($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::delete', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_empty_statement()
	{
		return array(
			array(''),
			array(new Database_Statement('')),
			array(new SQL_Expression('')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::execute_command
	 *
	 * @dataProvider  provider_empty_statement
	 *
	 * @param   string|Database_Statement|SQL_Expression    $value  Empty statement
	 */
	public function test_execute_command_empty($value)
	{
		$db = Database::factory();

		$this->assertSame(0, $db->execute_command($value));
	}

	/**
	 * @covers  Database_PostgreSQL::_execute
	 */
	public function test_execute_command_error()
	{
		$db = Database::factory();

		$this->setExpectedException(
			'Database_Exception',
			'syntax error',
			error_reporting() & E_WARNING
		);

		$db->execute_command('kohana invalid command');
	}

	/**
	 * @covers  Database_PostgreSQL::execute_insert
	 *
	 * @dataProvider  provider_empty_statement
	 *
	 * @param   string|Database_Statement|SQL_Expression    $value  Empty statement
	 */
	public function test_execute_insert_empty($value)
	{
		$db = Database::factory();

		$this->assertSame(array(0,0), $db->execute_insert($value, 'id'));
	}

	/**
	 * @covers  Database_PostgreSQL::execute_query
	 *
	 * @dataProvider  provider_empty_statement
	 *
	 * @param   string|Database_Statement|SQL_Expression    $value  Empty statement
	 */
	public function test_execute_query_empty($value)
	{
		$db = Database::factory();

		$this->assertNull($db->execute_query($value));
	}

	/**
	 * @covers  Database_PostgreSQL::_evaluate_query
	 */
	public function test_execute_query_error()
	{
		$db = Database::factory();

		$this->setExpectedException(
			'Database_Exception',
			'syntax error',
			error_reporting() & E_WARNING
		);

		$db->execute_query('kohana invalid query');
	}

	public function provider_identical()
	{
		return array(
			array(array('a', '=', 'b'), new Database_PostgreSQL_Identical('a', '=', 'b')),
			array(array('a', '<>', 'b'), new Database_PostgreSQL_Identical('a', '<>', 'b')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::identical
	 *
	 * @dataProvider    provider_identical
	 *
	 * @param   array                           $arguments
	 * @param   Database_PostgreSQL_Identical   $expected
	 */
	public function test_identical($arguments, $expected)
	{
		$this->assertEquals(
			$expected, call_user_func_array('Database_PostgreSQL::identical', $arguments)
		);
	}

	public function provider_insert()
	{
		return array(
			array(array(), new Database_PostgreSQL_DML_Insert),
			array(array('a'), new Database_PostgreSQL_DML_Insert('a')),
			array(
				array('a', array('b')),
				new Database_PostgreSQL_DML_Insert('a', array('b'))
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::insert
	 *
	 * @dataProvider    provider_insert
	 *
	 * @param   array                       $arguments
	 * @param   Database_PostgreSQL_DML_Insert  $expected
	 */
	public function test_insert($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::insert', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_parse_statement()
	{
		return array(
			array(new SQL_Expression(''), new Database_Statement('')),

			// data set #1
			array(
				new SQL_Expression('?', array('a')),
				new Database_Statement('$1', array('a'))
			),
			array(
				new SQL_Expression('?', array(new SQL_Expression('a'))),
				new Database_Statement('a')
			),
			array(
				new SQL_Expression('?', array(new SQL_Identifier('a'))),
				new Database_Statement('"a"')
			),

			// data set #4
			array(
				new SQL_Expression(':a', array(':a' => 'b')),
				new Database_Statement('$1', array('b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => new SQL_Expression('b'))),
				new Database_Statement('b')
			),
			array(
				new SQL_Expression(':a', array(':a' => new SQL_Identifier('b'))),
				new Database_Statement('"b"')
			),

			// data set #7
			array(
				new SQL_Expression('?', array(array())),
				new Database_Statement('')
			),
			array(
				new SQL_Expression('?', array(array('a', 'b'))),
				new Database_Statement('$1, $2', array('a', 'b'))
			),

			// data set #9
			array(
				new SQL_Expression('?', array(array(new SQL_Expression('a'), 'b'))),
				new Database_Statement('a, $1', array('b'))
			),
			array(
				new SQL_Expression('?', array(array(new SQL_Identifier('a'), 'b'))),
				new Database_Statement('"a", $1', array('b'))
			),

			// data set #11
			array(
				new SQL_Expression(':a', array(':a' => array('b', new SQL_Expression('c')))),
				new Database_Statement('$1, c', array('b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => array('b', new SQL_Identifier('c')))),
				new Database_Statement('$1, "c"', array('b'))
			),

			// data set #13
			array(
				new SQL_Expression('?', array(array(array('a', 'b')))),
				new Database_Statement('$1, $2', array('a', 'b'))
			),
			array(
				new SQL_Expression(':a', array(':a' => array(array('b', 'c')))),
				new Database_Statement('$1, $2', array('b', 'c'))
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::_parse
	 * @covers  Database_PostgreSQL::_parse_array
	 * @covers  Database_PostgreSQL::parse_statement
	 *
	 * @dataProvider    provider_parse_statement
	 *
	 * @param   SQL_Expression      $argument   Argument to the method
	 * @param   Database_Statement  $expected   Expected result
	 */
	public function test_parse_statement($argument, $expected)
	{
		$db = Database::factory();

		$this->assertEquals($expected, $db->parse_statement($argument));
	}

	/**
	 * @covers  Database_PostgreSQL::_parse_array
	 */
	public function test_parse_statement_bound_array()
	{
		$db = Database::factory();

		$var;
		$array[] =& $var;

		$expression = new SQL_Expression('?');
		$expression->bind(0, $array);

		$statement = $db->parse_statement($expression);

		$this->assertSame(array(0 => NULL), $statement->parameters());

		$var = 1;
		$this->assertSame(array(0 => 1), $statement->parameters());
	}

	/**
	 * @covers  Database_PostgreSQL::_parse
	 */
	public function test_parse_statement_bound_named()
	{
		$db = Database::factory();

		$expression = new SQL_Expression(':a');
		$expression->bind(':a', $var);

		$statement = $db->parse_statement($expression);

		$this->assertSame(array(0 => NULL), $statement->parameters());

		$var = 1;
		$this->assertSame(array(0 => 1), $statement->parameters());
	}

	/**
	 * @covers  Database_PostgreSQL::_parse
	 */
	public function test_parse_statement_bound_positional()
	{
		$db = Database::factory();

		$expression = new SQL_Expression('?');
		$expression->bind(0, $var);

		$statement = $db->parse_statement($expression);

		$this->assertSame(array(0 => NULL), $statement->parameters());

		$var = 1;
		$this->assertSame(array(0 => 1), $statement->parameters());
	}

	/**
	 * @covers  Database_PostgreSQL::prepare
	 */
	public function test_prepare()
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$name = $db->prepare(NULL, 'SELECT * FROM '.$table);

		$this->assertNotEquals('', $name, 'Returns a generated name');

		$result = $db->execute_query("SELECT * FROM pg_prepared_statements WHERE name = '$name'");
		$this->assertSame(1, $result->count(), 'Created successfully');
		$this->assertSame('f', $result->get('from_sql'), 'Definitely programmatic');

		$this->assertSame('asdf', $db->prepare('asdf', 'SELECT * FROM '.$table));
	}

	/**
	 * @covers  Database_PostgreSQL::prepare
	 */
	public function test_prepare_invalid()
	{
		$db = Database::factory();

		$this->setExpectedException('Database_Exception', 'syntax error', 42601);

		$db->prepare(NULL, 'kohana-invalid-sql');
	}

	public function provider_prepare_statement()
	{
		return array(
			array(
				new Database_Statement('SELECT $1::integer', array(60)),
				'kohana_6fcb347b3bead4838be84ef13f21a1b11dabb73c',
				'SELECT $1::integer',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ?', array(
					new SQL_Table($this->_table)
				)),
				'kohana_1ef3611cce5cf227d7967ce4f80d67b715b8089b',
				'DELETE FROM $table',
				array()
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE ?', array(
					new SQL_Table($this->_table),
					new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_9a6297e41a9edf4ae48684a1d92db8e2b365e0d8',
				'DELETE FROM $table WHERE "value" = $1',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE :a', array(
					new SQL_Table($this->_table),
					':a' => new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_9a6297e41a9edf4ae48684a1d92db8e2b365e0d8',
				'DELETE FROM $table WHERE "value" = $1',
				array(60)
			),

			array(
				new SQL_Expression('DELETE FROM ? WHERE :a AND :a', array(
					new SQL_Table($this->_table),
					':a' => new SQL_Conditions(new SQL_Column('value'), '=', 60)
				)),
				'kohana_9b6ddef92a7087faca26fbe06bda018711c012d1',
				'DELETE FROM $table WHERE "value" = $1 AND "value" = $1',
				array(60)
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::prepare_statement
	 *
	 * @dataProvider    provider_prepare_statement
	 *
	 * @param   Database_Statement|SQL_Expression   $argument   Argument to the method
	 * @param   string  $name   Expected name
	 * @param   string  $sql    Expected sql
	 * @param   array   $params Expected parameters
	 */
	public function test_prepare_statement($argument, $name, $sql, $params)
	{
		$db = Database::factory();
		$table = $db->quote_table($this->_table);

		$expected = new Database_PostgreSQL_Statement($db, $name, $params);
		$expected->statement = strtr($sql, array('$table' => $table));

		$this->assertEquals($expected, $db->prepare_statement($argument));
	}

	/**
	 * @covers  Database_PostgreSQL::quote_expression
	 */
	public function test_quote_expression()
	{
		$db = Database::factory();
		$expression = new SQL_Expression("SELECT :value::interval, 'yes':::type", array(':value' => '1 week', ':type' => new SQL_Expression('boolean')));

		$this->assertSame("SELECT '1 week'::interval, 'yes'::boolean", $db->quote_expression($expression));
	}

	/**
	 * @covers  Database_PostgreSQL::quote_expression
	 */
	public function test_quote_expression_placeholder_first()
	{
		$db = Database::factory();

		$this->assertSame('1', $db->quote_expression(new SQL_Expression('?', array(1))));
		$this->assertSame('2', $db->quote_expression(new SQL_Expression(':param', array(':param' => 2))));
	}

	public function provider_quote_literal()
	{
		return array(
			array(NULL, 'NULL'),
			array(FALSE, "'0'"),
			array(TRUE, "'1'"),

			array(0, '0'),
			array(-1, '-1'),
			array(51678, '51678'),
			array(12.345, '12.345000'),

			array('string', "'string'"),
			array("multiple\nlines", "'multiple\nlines'"),
			array("single'quote", "'single''quote'"),
			array("double\"quote", "'double\"quote'"),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::escape_literal
	 * @covers  Database_PostgreSQL::quote_literal
	 *
	 * @dataProvider    provider_quote_literal
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_quote_literal($value, $expected)
	{
		$db = Database::factory();

		$this->assertSame($expected, $db->quote_literal($value));
	}

	public function provider_quote_literal_binary()
	{
		return array(
			array(
				new Database_Binary("\x0"),
				"'\\000'",
				"'\\x00'",
			),
			array(
				new Database_Binary("\200\0\350"),
				"'\\200\\000\\350'",
				"'\\x8000e8'",
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::escape_literal
	 * @covers  Database_PostgreSQL::quote_literal
	 *
	 * @dataProvider    provider_quote_literal_binary
	 *
	 * @param   Database_Binary $value  Argument to the method
	 * @param   string          $escape Expected result in escape format
	 * @param   string          $hex    Expected result in hex format
	 */
	public function test_quote_literal_binary($value, $escape, $hex)
	{
		$db = Database::factory();
		$result = $db->quote($value);

		if ($db->execute_query('SHOW standard_conforming_strings')->get() === 'off')
		{
			// When standard_conforming_strings is off, backslashes are escaped
			$escape = strtr($escape, array('\\' => '\\\\'));
			$hex = strtr($hex, array('\\' => '\\\\'));
		}

		if (version_compare($db->version(), '9.0', '<'))
		{
			// Escape is the only format before PostgreSQL 9.0
			$this->assertSame($escape, $result);
		}
		else
		{
			// Hex is the default format since PostgreSQL 9.0
			$this->assertSame($hex, $result);
		}
	}

	/**
	 * @covers  Database_PostgreSQL::connect
	 * @covers  Database_PostgreSQL::disconnect
	 */
	public function test_reconnect()
	{
		$db = Database::factory();

		$db->connect();
		$db->disconnect();
		$db->connect();
	}

	public function provider_select()
	{
		return array(
			array(array(), new Database_PostgreSQL_DML_Select),
			array(array(array('a')), new Database_PostgreSQL_DML_Select(array('a'))),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::select
	 *
	 * @dataProvider    provider_select
	 *
	 * @param   array                       $arguments
	 * @param   Database_PostgreSQL_DML_Select  $expected
	 */
	public function test_select($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::select', $arguments);
		$this->assertEquals($expected, $statement);
	}

	public function provider_update()
	{
		return array(
			array(array(), new Database_PostgreSQL_DML_Update),
			array(array('a'), new Database_PostgreSQL_DML_Update('a')),
			array(array('a', 'b'), new Database_PostgreSQL_DML_Update('a', 'b')),
			array(
				array('a', 'b', array('c' => 'd')),
				new Database_PostgreSQL_DML_Update('a', 'b', array('c' => 'd'))
			),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::update
	 *
	 * @dataProvider    provider_update
	 *
	 * @param   array                       $arguments
	 * @param   Database_PostgreSQL_DML_Update  $expected
	 */
	public function test_update($arguments, $expected)
	{
		$statement = call_user_func_array('Database_PostgreSQL::update', $arguments);
		$this->assertEquals($expected, $statement);
	}
}
