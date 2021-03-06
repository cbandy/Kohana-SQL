<?php
/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgresSQL_Introspection_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'PostgreSQL extension not installed'
			);

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError(
				'Database not configured for PostgreSQL'
			);
	}

	protected $_information_schema_defaults = array(
		'column_name'       => NULL,
		'ordinal_position'  => NULL,
		'column_default'    => NULL,
		'is_nullable'       => NULL,
		'data_type'         => NULL,
		'character_maximum_length'  => NULL,
		'numeric_precision' => NULL,
		'numeric_scale'     => NULL,
		'datetime_precision' => NULL,
	);

	protected $_table = 'kohana_introspect_test_table';

	public function setUp()
	{
		$db = Database::factory();

		$db->execute_command(
			'DROP TABLE IF EXISTS '.$db->quote_table($this->_table)
		);
	}

	public function provider_table_columns_argument()
	{
		return array(
			array(array($this->_table)),
			array(new SQL_Table($this->_table)),
		);
	}

	/**
	 * Test different arguments to table_columns().
	 *
	 * @covers  Database_PostgreSQL::table_columns
	 *
	 * @dataProvider    provider_table_columns_argument
	 *
	 * @param   mixed   $input  Argument to the method
	 */
	public function test_table_columns_argument($input)
	{
		$db = Database::factory();
		$db->execute_command(
			'CREATE TABLE '.$db->quote_table($this->_table).' (field boolean)'
		);

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'data_type' => 'boolean',
			'ordinal_position' => '1',
			'is_nullable' => 'YES',
		));

		$result = $db->table_columns($input);

		$this->assertSame($expected, $result['field']);
	}

	public function provider_table_columns_constraints()
	{
		return array(
			array('boolean DEFAULT NULL', array(
				'data_type' => 'boolean',
				'is_nullable' => 'YES',
			)),
			array('boolean DEFAULT false', array(
				'column_default' => 'false',
				'data_type' => 'boolean',
				'is_nullable' => 'YES',
			)),
			array('boolean DEFAULT true', array(
				'column_default' => 'true',
				'data_type' => 'boolean',
				'is_nullable' => 'YES',
			)),

			array('integer DEFAULT NULL', array(
				'data_type' => 'integer',
				'is_nullable' => 'YES',
				'numeric_precision' => '32',
				'numeric_scale' => '0',
			)),
			array('integer DEFAULT 0', array(
				'column_default' => '0',
				'data_type' => 'integer',
				'is_nullable' => 'YES',
				'numeric_precision' => '32',
				'numeric_scale' => '0',
			)),
			array('integer DEFAULT 1', array(
				'column_default' => '1',
				'data_type' => 'integer',
				'is_nullable' => 'YES',
				'numeric_precision' => '32',
				'numeric_scale' => '0',
			)),

			array('real DEFAULT random()', array(
				'column_default' => "random()",
				'data_type' => 'real',
				'is_nullable' => 'YES',
				'numeric_precision' => '24',
			)),

			array("varchar(1) DEFAULT NULL", array(
				'character_maximum_length' => '1',
				'column_default' => 'NULL::character varying',
				'data_type' => 'character varying',
				'is_nullable' => 'YES',
			)),
			array("varchar(1) DEFAULT ''", array(
				'character_maximum_length' => '1',
				'column_default' => "''::character varying",
				'data_type' => 'character varying',
				'is_nullable' => 'YES',
			)),
			array("varchar(1) DEFAULT 'a'", array(
				'character_maximum_length' => '1',
				'column_default' => "'a'::character varying",
				'data_type' => 'character varying',
				'is_nullable' => 'YES',
			)),

			array('boolean NOT NULL', array(
				'data_type' => 'boolean',
				'is_nullable' => 'NO',
			)),
			array('integer NOT NULL', array(
				'data_type' => 'integer',
				'is_nullable' => 'NO',
				'numeric_precision' => '32',
				'numeric_scale' => '0',
			)),
			array("varchar(1) NOT NULL", array(
				'character_maximum_length' => '1',
				'data_type' => 'character varying',
				'is_nullable' => 'NO',
			)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::table_columns
	 *
	 * @dataProvider    provider_table_columns_constraints
	 *
	 * @param   string  $column     Column definition
	 * @param   array   $expected   Expected column attributes
	 */
	public function test_table_columns_constraints($column, $expected)
	{
		$db = Database::factory();
		$db->execute_command(
			'CREATE TABLE '.$db->quote_table($this->_table)." (field $column)"
		);

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'ordinal_position' => '1',
		), $expected);

		$result = $db->table_columns($this->_table);

		$this->assertSame($expected, $result['field']);
	}

	/**
	 * @covers  Database_PostgreSQL::table_columns
	 */
	public function test_table_columns_no_table()
	{
		$db = Database::factory();

		$this->assertSame(
			array(), $db->table_columns('kohana-table-does-not-exist')
		);
	}

	public function provider_table_columns_types()
	{
		return array(

			// Binary

			array('bytea', array(
				'data_type' => 'bytea',
			)),

			// Bit

			array('bit(20)', array(
				'data_type' => 'bit',
				'character_maximum_length' => '20',
			)),
			array('varbit(10)', array(
				'data_type' => 'bit varying',
				'character_maximum_length' => '10',
			)),
			array('varbit', array(
				'data_type' => 'bit varying',
			)),

			// Boolean

			array('boolean', array(
				'data_type' => 'boolean',
			)),

			// Character

			array('char(30)', array(
				'data_type' => 'character',
				'character_maximum_length' => '30',
			)),
			array('varchar(40)', array(
				'data_type' => 'character varying',
				'character_maximum_length' => '40',
			)),
			array('varchar', array(
				'data_type' => 'character varying',
			)),

			array('text', array(
				'data_type' => 'text',
			)),

			// Date and Time

			array('date', array(
				'data_type' => 'date',
				'datetime_precision' => '0',
			)),
			array('interval(5)', array(
				'data_type' => 'interval',
				'datetime_precision' => '5',
			)),
			array('interval', array(
				'data_type' => 'interval',
				'datetime_precision' => '6',
			)),
			array('time(3)', array(
				'data_type' => 'time without time zone',
				'datetime_precision' => '3',
			)),
			array('time', array(
				'data_type' => 'time without time zone',
				'datetime_precision' => '6',
			)),
			array('time with time zone', array(
				'data_type' => 'time with time zone',
				'datetime_precision' => '6',
			)),
			array('timestamp(2)', array(
				'data_type' => 'timestamp without time zone',
				'datetime_precision' => '2',
			)),
			array('timestamp', array(
				'data_type' => 'timestamp without time zone',
				'datetime_precision' => '6',
			)),
			array('timestamp with time zone', array(
				'data_type' => 'timestamp with time zone',
				'datetime_precision' => '6',
			)),

			// Floating Point

			array('double precision', array(
				'data_type' => 'double precision',
				'numeric_precision' => '53',
			)),
			array('real', array(
				'data_type' => 'real',
				'numeric_precision' => '24',
			)),

			// Geometry

			array('box', array(
				'data_type' => 'box',
			)),
			array('circle', array(
				'data_type' => 'circle',
			)),
			array('line', array(
				'data_type' => 'line',
			)),
			array('lseg', array(
				'data_type' => 'lseg',
			)),
			array('path', array(
				'data_type' => 'path',
			)),
			array('point', array(
				'data_type' => 'point',
			)),
			array('polygon', array(
				'data_type' => 'polygon',
			)),

			// Integer

			array('integer', array(
				'data_type' => 'integer',
				'numeric_precision' => '32',
				'numeric_scale' => '0',
			)),
			array('smallint', array(
				'data_type' => 'smallint',
				'numeric_precision' => '16',
				'numeric_scale' => '0',
			)),
			array('bigint', array(
				'data_type' => 'bigint',
				'numeric_precision' => '64',
				'numeric_scale' => '0',
			)),

			// Network

			array('cidr', array(
				'data_type' => 'cidr',
			)),
			array('inet', array(
				'data_type' => 'inet',
			)),
			array('macaddr', array(
				'data_type' => 'macaddr',
			)),

			// Numeric

			array('numeric(13,7)', array(
				'data_type' => 'numeric',
				'numeric_precision' => '13',
				'numeric_scale' => '7',
			)),
			array('numeric(5)', array(
				'data_type' => 'numeric',
				'numeric_precision' => '5',
				'numeric_scale' => '0',
			)),
			array('numeric', array(
				'data_type' => 'numeric',
			)),

			// Text Search

			array('tsquery', array(
				'data_type' => 'tsquery',
			)),
			array('tsvector', array(
				'data_type' => 'tsvector',
			)),

			// Miscellaneous

			array('money', array(
				'data_type' => 'money',
			)),
			array('txid_snapshot', array(
				'data_type' => 'txid_snapshot',
			)),
			array('uuid', array(
				'data_type' => 'uuid',
			)),
			array('xml', array(
				'data_type' => 'xml',
			)),
		);
	}

	/**
	 * @covers  Database_PostgreSQL::table_columns
	 *
	 * @dataProvider    provider_table_columns_types
	 *
	 * @param   string  $column     Column data type
	 * @param   array   $expected   Expected column attributes
	 */
	public function test_table_columns_types($column, $expected)
	{
		$db = Database::factory();
		$db->execute_command(
			'CREATE TABLE '.$db->quote_table($this->_table)." (field $column)"
		);

		$expected = array_merge($this->_information_schema_defaults, array(
			'column_name' => 'field',
			'ordinal_position' => '1',
			'is_nullable' => 'YES',
		), $expected);

		$result = $db->table_columns($this->_table);

		$this->assertSame($expected, $result['field']);
	}
}
