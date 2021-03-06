<?php

/**
 * [MySQL](http://www.mysql.com/) connection and expression factory.
 *
 * [!!] Requires MySQL >= 5.0.7
 *
 *  Configuration Option  | Type    | Description
 *  --------------------  | ----    | -----------
 *  charset               | string  | Character set
 *  table_prefix          | string  | Table prefix
 *  connection.database   | string  |
 *  connection.flags      | integer | Combination of [client constants][], e.g. MYSQL_CLIENT_SSL
 *  connection.hostname   | string  | Server address or path to a local socket. Use `'127.0.0.1'` to [connect locally using TCP/IP][loopback]
 *  connection.password   | string  |
 *  connection.persistent | boolean | Use the PHP connection pool
 *  connection.port       | integer | Server port
 *  connection.username   | string  |
 *  connection.variables  | array   | [System variables][] as "key => value" pairs
 *
 * [Client constants]: http://www.php.net/manual/mysql.constants
 * [Loopback]:         http://dev.mysql.com/doc/en/can-not-connect-to-server.html
 * [System variables]: http://dev.mysql.com/doc/en/dynamic-system-variables.html
 *
 * @link http://www.php.net/manual/book.mysql
 *
 * @package     RealDatabase
 * @subpackage  MySQL
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_MySQL extends Database
	implements Database_iEscape, Database_iIntrospect
{
	/**
	 * Maxium number of unicode characters allowed in an alias
	 *
	 * @link http://dev.mysql.com/doc/en/identifiers.html
	 */
	const MAX_LENGTH_ALIAS = 256;

	/**
	 * Maxium number of unicode characters allowed in a schema object name
	 *
	 * @link http://dev.mysql.com/doc/en/identifiers.html
	 */
	const MAX_LENGTH_IDENTIFIER = 64;

	/**
	 * @see Database_MySQL::_select_database()
	 *
	 * @var array   Active databases
	 */
	protected static $_databases;

	/**
	 * Create an ALTER TABLE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @return  Database_MySQL_DDL_Alter_Table
	 */
	public static function alter_table($name = NULL)
	{
		return new Database_MySQL_DDL_Alter_Table($name);
	}

	/**
	 * Create a CREATE INDEX statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name       Converted to SQL_Identifier
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table      Converted to SQL_Table
	 * @param   array                                       $columns    List of columns, each converted to SQL_Column
	 * @return  Database_MySQL_DDL_Create_Index
	 */
	public static function create_index($name = NULL, $table = NULL, $columns = NULL)
	{
		return new Database_MySQL_DDL_Create_Index($name, $table, $columns);
	}

	/**
	 * Create a CREATE TABLE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @return  Database_MySQL_DDL_Create_Table
	 */
	public static function create_table($name = NULL)
	{
		return new Database_MySQL_DDL_Create_Table($name);
	}

	/**
	 * Create a CREATE VIEW statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Table
	 * @param   SQL_Expression                              $query
	 * @return  Database_MySQL_DDL_Create_View
	 */
	public static function create_view($name = NULL, $query = NULL)
	{
		return new Database_MySQL_DDL_Create_View($name, $query);
	}

	/**
	 * Create a column expression.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $name   Converted to SQL_Column
	 * @param   mixed                                       $type   Converted to SQL_Expression
	 * @return  Database_MySQL_DDL_Column
	 */
	public static function ddl_column($name = NULL, $type = NULL)
	{
		return new Database_MySQL_DDL_Column($name, $type);
	}

	/**
	 * Create an ENUM expression.
	 *
	 * @param   array   $values
	 * @return  Database_MySQL_DDL_Enum
	 */
	public static function ddl_enum($values = NULL)
	{
		return new Database_MySQL_DDL_Enum($values);
	}

	/**
	 * Create a SET expression.
	 *
	 * @param   array   $values
	 * @return  Database_MySQL_DDL_Set
	 */
	public static function ddl_set($values = NULL)
	{
		return new Database_MySQL_DDL_Set($values);
	}

	/**
	 * Create a DELETE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @return  Database_MySQL_DML_Delete
	 */
	public static function delete($table = NULL, $alias = NULL)
	{
		return new Database_MySQL_DML_Delete($table, $alias);
	}

	/**
	 * Create an expression for comparing whether or not two values are
	 * distinct.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Equality operator
	 * @param   mixed   $right      Right operand
	 * @return  Database_MySQL_Identical
	 */
	public static function identical($left, $operator, $right)
	{
		return new Database_MySQL_Identical($left, $operator, $right);
	}

	/**
	 * Create a SELECT statement.
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs
	 * @return  Database_MySQL_DML_Select
	 */
	public static function select($columns = NULL)
	{
		return new Database_MySQL_DML_Select($columns);
	}

	/**
	 * Create an UPDATE statement.
	 *
	 * @param   array|string|SQL_Expression|SQL_Identifier  $table  Converted to SQL_Table
	 * @param   array|string|SQL_Expression|SQL_Identifier  $alias  Converted to SQL_Identifier
	 * @param   array                                       $values Hash of (column => value) assignments
	 * @return  Database_MySQL_DML_Update
	 */
	public static function update($table = NULL, $alias = NULL, $values = NULL)
	{
		return new Database_MySQL_DML_Update($table, $alias, $values);
	}

	/**
	 * @var resource    Link identifier
	 */
	protected $_connection;

	/**
	 * @var string  Persistent connection hash according to PHP driver
	 */
	protected $_connection_id;

	protected $_quote_left = '`';

	protected $_quote_right = '`';

	/**
	 * @var Database_Savepoint_Stack    Stack of savepoint names
	 */
	protected $_savepoints;

	/**
	 * Execute a statement after connecting
	 *
	 * @throws  Database_Exception
	 * @param   string  $statement  SQL statement
	 * @return  resource|TRUE   Result resource for a query or TRUE for a command
	 */
	protected function _execute($statement)
	{
		if ( ! $this->_connection)
		{
			$this->connect();
		}
		elseif ( ! empty($this->_config['connection']['persistent'])
			AND $this->_config['connection']['database']
				!== Database_MySQL::$_databases[$this->_connection_id])
		{
			// Select database on persistent connections
			$this->_select_database($this->_config['connection']['database']);
		}

		if (Kohana::$profiling)
		{
			$benchmark = Profiler::start(
				'Database ('.$this->_name.')', $statement
			);
		}

		try
		{
			// Raises E_WARNING upon error
			$result = mysql_query($statement, $this->_connection);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => $e->getMessage()),
				$e->getCode()
			);
		}

		if ($result === FALSE)
		{
			if (isset($benchmark))
			{
				Profiler::delete($benchmark);
			}

			throw new Database_Exception(
				':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection)
			);
		}

		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}

		return $result;
	}

	/**
	 * Set and track the active database
	 *
	 * @throws  Database_Exception
	 * @param   string  $database   Database
	 * @return  void
	 */
	protected function _select_database($database)
	{
		if ( ! mysql_select_db($database, $this->_connection))
			throw new Database_Exception(
				':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection)
			);

		Database_MySQL::$_databases[$this->_connection_id] = $database;
	}

	public function begin($name = NULL)
	{
		if (count($this->_savepoints))
		{
			// Nested transaction
			return $this->savepoint($name);
		}

		$this->_execute('START TRANSACTION');

		if ($name === NULL)
		{
			$name = 'kohana_txn_'.count($this->_savepoints);
		}

		$this->_savepoints->push($name);

		return $name;
	}

	/**
	 * Set the connection character set.
	 *
	 * @throws  Database_Exception
	 * @param   string  $charset    Character set
	 * @return  void
	 */
	public function charset($charset)
	{
		$this->_connection or $this->connect();

		if ( ! mysql_set_charset($charset, $this->_connection))
			throw new Database_Exception(
				':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection)
			);
	}

	public function commit($name = NULL)
	{
		if ($name === NULL OR $this->_savepoints->position($name) === 1)
		{
			$this->_execute('COMMIT');

			// Reset the savepoint stack
			$this->_savepoints->reset();
		}
		else
		{
			$this->_execute(
				'RELEASE SAVEPOINT '
				.$this->_quote_left.$name.$this->_quote_right
			);

			// Remove all savepoints after this one
			$this->_savepoints->pop_until($name);

			// Remove this savepoint
			$this->_savepoints->pop();
		}
	}

	/**
	 * Connect
	 *
	 * @link http://php.net/manual/function.mysql-connect
	 * @link http://php.net/manual/ini.core#ini.sql.safe-mode
	 *
	 * @todo SQL Safe Mode can be supported, but only for _one_ MySQL instance
	 *
	 * @throws  Database_Exception
	 * @return  void
	 */
	public function connect()
	{
		extract($this->_config['connection']);

		if ( ! $this->_connection_id)
		{
			if ( ! isset($flags))
			{
				$flags = $this->_config['connection']['flags'] = 0;
			}

			if ( ! empty($port))
			{
				$hostname = $this->_config['connection']['hostname']
					.= ':'.$port;
			}

			$this->_connection_id
				= $hostname.'_'.$username.'_'.$password.'_'.$flags;
		}

		try
		{
			// Raises E_NOTICE when sql.safe_mode is set
			// Raises E_WARNING upon error
			$this->_connection = empty($persistent)
				? mysql_connect($hostname, $username, $password, TRUE, $flags)
				: mysql_pconnect($hostname, $username, $password, $flags);
		}
		catch (Exception $e)
		{
			// @codeCoverageIgnoreStart
			throw new Database_Exception(
				':error',
				array(':error' => $e->getMessage()),
				$e->getCode()
			);
			// @codeCoverageIgnoreEnd
		}

		if ( ! is_resource($this->_connection))
			throw new Database_Exception(
				'Unable to connect to MySQL ":name"',
				array(':name' => $this->_name)
			);

		$this->_select_database($database);

		if ( ! empty($this->_config['charset']))
		{
			$this->charset($this->_config['charset']);
		}

		if ( ! empty($variables))
		{
			foreach ($variables as $variable => $value)
			{
				$variables[$variable] =
					'SESSION '.$variable.' = '.$this->quote_literal($value);
			}

			$this->_execute('SET '.implode(', ', $variables));
		}

		// Initialize the savepoint stack
		$this->_savepoints = new Database_Savepoint_Stack;
	}

	public function disconnect()
	{
		if (is_resource($this->_connection))
		{
			mysql_close($this->_connection);

			$this->_connection = NULL;
		}
	}

	/**
	 * Return information about a MySQL data type
	 *
	 * @link http://dev.mysql.com/doc/en/data-types.html
	 *
	 * @param   string  $type       SQL data type
	 * @param   string  $attribute  Attribute to return
	 * @return  array|mixed Array of attributes or an attribute value
	 */
	public function datatype($type, $attribute = NULL)
	{
		static $types = array
		(
			'blob'                      => array('type' => 'binary'),
			'bool'                      => array('type' => 'boolean'),
			'bigint unsigned'           => array('type' => 'integer', 'min' => '0', 'max' => '18446744073709551615'),
			'datetime'                  => array('type' => 'string'),
			'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'double'                    => array('type' => 'float'),
			'double precision unsigned' => array('type' => 'float', 'min' => '0'),
			'double unsigned'           => array('type' => 'float', 'min' => '0'),
			'enum'                      => array('type' => 'string'),
			'fixed'                     => array('type' => 'float', 'exact' => TRUE),
			'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'float unsigned'            => array('type' => 'float', 'min' => '0'),
			'geometry'                  => array('type' => 'binary'),
			'geometrycollection'        => array('type' => 'binary'),
			'int unsigned'              => array('type' => 'integer', 'min' => '0', 'max' => '4294967295'),
			'integer unsigned'          => array('type' => 'integer', 'min' => '0', 'max' => '4294967295'),
			'linestring'                => array('type' => 'binary'),
			'longblob'                  => array('type' => 'binary'),
			'longtext'                  => array('type' => 'string'),
			'mediumblob'                => array('type' => 'binary'),
			'mediumint'                 => array('type' => 'integer', 'min' => '-8388608', 'max' => '8388607'),
			'mediumint unsigned'        => array('type' => 'integer', 'min' => '0', 'max' => '16777215'),
			'mediumtext'                => array('type' => 'string'),
			'multilinestring'           => array('type' => 'binary'),
			'multipolygon'              => array('type' => 'binary'),
			'multipoint'                => array('type' => 'binary'),
			'national varchar'          => array('type' => 'string'),
			'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
			'nvarchar'                  => array('type' => 'string'),
			'polygon'                   => array('type' => 'binary'),
			'point'                     => array('type' => 'binary'),
			'real unsigned'             => array('type' => 'float', 'min' => '0'),
			'set'                       => array('type' => 'string'),
			'smallint unsigned'         => array('type' => 'integer', 'min' => '0', 'max' => '65535'),
			'text'                      => array('type' => 'string'),
			'tinyblob'                  => array('type' => 'binary'),
			'tinyint'                   => array('type' => 'integer', 'min' => '-128', 'max' => '127'),
			'tinyint unsigned'          => array('type' => 'integer', 'min' => '0', 'max' => '255'),
			'tinytext'                  => array('type' => 'string'),
			'year'                      => array('type' => 'string'),
		);

		// Strip ZEROFILL attribute
		$type = str_replace(' zerofill', '', $type);

		if ( ! isset($types[$type]))
			return parent::datatype($type, $attribute);

		if ($attribute !== NULL)
			return @$types[$type][$attribute];

		return $types[$type];
	}

	public function escape_literal($value)
	{
		$this->_connection or $this->connect();

		$value = mysql_real_escape_string( (string) $value, $this->_connection);

		return "'$value'";
	}

	public function execute_command($statement)
	{
		if ( ! is_string($statement))
		{
			if ( ! $statement instanceof Database_Statement)
			{
				$statement = $this->quote($statement);
			}
			elseif ( ! $parameters = $statement->parameters())
			{
				$statement = (string) $statement;
			}
			else
			{
				// This is convoluted, but a prepared statement is the only way
				// to pass parameters using the MySQL driver.
				$statement = new Database_MySQL_Statement(
					$this,
					$this->prepare(NULL, (string) $statement),
					$parameters
				);

				return $statement->execute_command();
			}
		}

		if (empty($statement))
			return 0;

		$result = $this->_execute($statement);

		if (is_resource($result))
		{
			mysql_free_result($result);
		}

		return mysql_affected_rows($this->_connection);
	}

	/**
	 * Execute an INSERT statement, returning the number of affected rows and
	 * the AUTO_INCREMENT of the first row.
	 *
	 * @throws  Database_Exception
	 * @param   string|Database_Statement|SQL_Expression    $statement  SQL insert
	 * @param   array|string|SQL_Expression|SQL_Identifier  $identity   Ignored
	 * @return  array   List including number of affected rows and the AUTO_INCREMENT of the first row
	 */
	public function execute_insert($statement, $identity)
	{
		$rows = $this->execute_command($statement);
		$result = $this->_connection ? mysql_insert_id($this->_connection) : 0;

		return array($rows, $result);
	}

	public function execute_query($statement, $as_object = FALSE, $arguments = array())
	{
		if ( ! is_string($statement))
		{
			if ( ! $statement instanceof Database_Statement)
			{
				$statement = $this->quote($statement);
			}
			elseif ( ! $parameters = $statement->parameters())
			{
				$statement = (string) $statement;
			}
			else
			{
				// This is convoluted, but a prepared statement is the only way
				// to pass parameters using the MySQL driver.
				$statement = new Database_MySQL_Statement(
					$this,
					$this->prepare(NULL, (string) $statement),
					$parameters
				);

				return $statement->execute_query($as_object, $arguments);
			}
		}

		if (empty($statement))
			return NULL;

		$result = $this->_execute($statement);

		if (is_bool($result))
			return NULL;

		return new Database_MySQL_Result($result, $as_object, $arguments);
	}

	/**
	 * Check whether or not the connection to the server is working.
	 *
	 *     if ( ! $db->ping()) $db->connect();
	 *
	 * @return  boolean
	 */
	public function ping()
	{
		return ($this->_connection AND mysql_ping($this->_connection));
	}

	/**
	 * Create a prepared statement after connecting.
	 *
	 * @link http://dev.mysql.com/doc/en/prepare.html
	 *
	 * @throws  Database_Exception
	 * @param   string  $name       Statement name or NULL to have one generated
	 * @param   string  $statement  SQL statement
	 * @return  string  Statement name
	 */
	public function prepare($name, $statement)
	{
		if ($name === NULL)
		{
			$name = 'kohana_'.sha1($statement);
		}

		$this->_execute(
			'PREPARE '.$this->quote_identifier($name)
			.' FROM '.$this->quote_literal($statement)
		);

		return $name;
	}

	/**
	 * Created a prepared statement from a MySQL-compatible [Database_Statement]
	 * or a generic [SQL_Expression].
	 *
	 * @uses Database_MySQL::prepare()
	 *
	 * @throws  Database_Exception
	 * @param   Database_Statement|SQL_Expression   $statement  SQL statement
	 * @return  Database_MySQL_Statement
	 */
	public function prepare_statement($statement)
	{
		if ( ! $statement instanceof Database_Statement)
		{
			$statement = $this->parse_statement($statement);
		}

		$name = $this->prepare(NULL, (string) $statement);

		$result = new Database_MySQL_Statement($this, $name, $statement->parameters());
		$result->statement = (string) $statement;

		return $result;
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @uses Database_MySQL::escape_literal()
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function quote_literal($value)
	{
		if (is_object($value) OR is_string($value))
			return $this->escape_literal($value);

		return parent::quote_literal($value);
	}

	public function rollback($name = NULL)
	{
		if ($name === NULL OR $this->_savepoints->position($name) === 1)
		{
			$this->_execute('ROLLBACK');

			// Reset the savepoint stack
			$this->_savepoints->reset();
		}
		else
		{
			$this->_execute(
				'ROLLBACK TO '.$this->_quote_left.$name.$this->_quote_right
			);

			// Remove all savepoints after this one
			$this->_savepoints->pop_until($name);
		}
	}

	public function savepoint($name = NULL)
	{
		if ($name === NULL)
		{
			$name = 'kohana_txn_'.count($this->_savepoints);
		}

		$this->_execute(
			'SAVEPOINT '.$this->_quote_left.$name.$this->_quote_right
		);

		$this->_savepoints->push($name);

		return $name;
	}

	/**
	 * Retrieve the tables of a schema in a format almost identical to that of
	 * the Tables table of the SQL-92 Information Schema. Includes four non-
	 * standard fields: `engine`, `auto_increment`, `table_collation` and
	 * `table_comment`.
	 *
	 * @link http://dev.mysql.com/doc/en/tables-table.html
	 *
	 * @param   array|string|SQL_Identifier $schema Converted to SQL_Identifier. NULL for the default schema.
	 * @return  array
	 */
	public function schema_tables($schema = NULL)
	{
		if ( ! $schema)
		{
			// Use configured default schema
			$schema = $this->_config['connection']['database'];
		}
		else
		{
			if ( ! $schema instanceof SQL_Identifier)
			{
				// Convert to identifier
				$schema = new SQL_Identifier($schema);
			}

			$schema = $schema->name;
		}

		$sql =
			'SELECT table_name, table_type,'
			.'   engine, auto_increment, table_collation, table_comment'
			.' FROM information_schema.tables WHERE table_schema = '
			.$this->quote_literal($schema);

		if ( ! $this->_table_prefix)
		{
			// No table prefix
			return $this->execute_query($sql)->as_array('table_name');
		}

		// Filter on table prefix. Using quote_literal() would double-escape
		// the backslash.
		$sql .= " AND table_name LIKE '"
			.strtr($this->_table_prefix, array('_' => '\_', '%' => '\%'))
			."%'";

		$prefix = strlen($this->_table_prefix);
		$result = array();

		foreach ($this->execute_query($sql) as $table)
		{
			// Strip table prefix from table name
			$table['table_name'] = substr($table['table_name'], $prefix);
			$result[$table['table_name']] = $table;
		}

		return $result;
	}

	/**
	 * Retrieve the columns of a table in a format almost identical to that of
	 * the Columns table of the SQL-92 Information Schema. Includes five
	 * non-standard fields: `column_type`, `column_key`, `extra`, `privileges`
	 * and `column_comment`.
	 *
	 * ENUM and SET also have their possible values extracted into `options`.
	 *
	 * MySQL does not allow column defaults to be defined using expressions.
	 * As a consequence, `column_default` contains the column's default value
	 * rather than its definition. It is not possible to distinguish between
	 * a column without a DEFAULT definition and a column with a DEFAULT value
	 * of NULL.
	 *
	 * @link http://dev.mysql.com/doc/en/columns-table.html
	 * @link http://dev.mysql.com/doc/en/data-type-defaults.html
	 *
	 * @param   array|string|SQL_Identifier $table  Converted to SQL_Table unless SQL_Identifier
	 * @return  array
	 */
	public function table_columns($table)
	{
		if ( ! $table instanceof SQL_Identifier)
		{
			// Convert to table
			$table = new SQL_Table($table);
		}

		if ( ! $schema = $table->namespace)
		{
			// Use configured default schema
			$schema = $this->_config['connection']['database'];
		}

		// Only add table prefix to SQL_Table (exclude from SQL_Identifier)
		$table = ($table instanceof SQL_Table)
			? ($this->_table_prefix.$table->name)
			: $table->name;

		$result =
			'SELECT column_name, ordinal_position, column_default, is_nullable,'
			.'   data_type, character_maximum_length,'
			.'   numeric_precision, numeric_scale, collation_name,'
			.'   column_type, column_key, extra, privileges, column_comment'
			.' FROM information_schema.columns'
			.' WHERE table_schema = '.$this->quote_literal($schema)
			.'   AND table_name = '.$this->quote_literal($table);

		$result = $this->execute_query($result)->as_array('column_name');

		foreach ($result as & $column)
		{
			if ($column['data_type'] === 'enum'
				OR $column['data_type'] === 'set')
			{
				$open = strpos($column['column_type'], '(');
				$close = strrpos($column['column_type'], ')', $open);

				// Text between parentheses without single quotes
				$column['options'] = explode(
					"','",
					substr(
						$column['column_type'],
						$open + 2,
						$close - 3 - $open
					)
				);
			}
			elseif (strlen($column['column_type']) > 8)
			{
				// Test for UNSIGNED or UNSIGNED ZEROFILL
				if (substr_compare($column['column_type'], 'unsigned', -8) === 0
					OR substr_compare($column['column_type'], 'unsigned', -17, 8) === 0)
				{
					$column['data_type'] .= ' unsigned';
				}
			}
		}

		return $result;
	}
}
