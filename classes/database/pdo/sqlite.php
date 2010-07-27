<?php

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Drivers
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_PDO_SQLite extends Database_PDO implements Database_iEscape, Database_iInsert
{
	/**
	 * Create an INSERT command
	 *
	 * @param   mixed   $table      Converted to Database_Table
	 * @param   array   $columns    Each element converted to Database_Column
	 * @return  Database_Command_Insert_Multiple
	 */
	public static function insert($table = NULL, $columns = NULL)
	{
		return new Database_Command_Insert_Multiple($table, $columns);
	}

	/**
	 * Create a PDO connection for SQLite
	 *
	 *  Configuration Option  | Type    | Description
	 *  --------------------  | ----    | -----------
	 *  charset               | string  | Character set
	 *  pragmas               | array   | [PRAGMA][] settings as "key => value" pairs
	 *  profiling             | boolean | Enable execution profiling
	 *  schema                | string  | Table prefix
	 *  connection.dsn        | string  | Full DSN or a predefined DSN name
	 *  connection.options    | array   | PDO options
	 *  connection.persistent | boolean | Use the PHP connection pool
	 *  connection.uri        | string  | URI to a file containing the DSN
	 *
	 * *[DSN]: Data Source Name
	 * *[URI]: Uniform Resource Identifier
	 * [PRAGMA]: http://www.sqlite.org/pragma.html
	 *
	 * @link http://php.net/manual/ref.pdo-sqlite.connection PDO SQLite DSN
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name   Instance name
	 * @param   array   $config Configuration
	 */
	protected function __construct($name, $config)
	{
		parent::__construct($name, $config);

		$this->_config['connection']['username'] = NULL;
		$this->_config['connection']['password'] = NULL;
	}

	public function charset($charset)
	{
		$this->execute_command('PRAGMA encoding = "'.$charset.'"');
	}

	public function connect()
	{
		parent::connect();

		if ( ! empty($this->_config['pragmas']))
		{
			foreach ($this->_config['pragmas'] as $pragma => $value)
			{
				$this->execute_command('PRAGMA '.$pragma.' = '.$this->quote_literal($value));
			}
		}
	}

	/**
	 * Return information about a SQLite data type
	 *
	 * @link http://www.sqlite.org/datatype3.html
	 *
	 * @param   string  $type       SQL data type
	 * @param   string  $attribute  Attribute to return
	 * @return  array|mixed Array of attributes or an attribute value
	 */
	public function datatype($type, $attribute = NULL)
	{
		if (strpos($type, 'int') !== FALSE)
		{
			$result = array('type' => 'integer');
		}
		elseif (strpos($type, 'char') !== FALSE
			OR strpos($type, 'clob') !== FALSE
			OR strpos($type, 'text') !== FALSE)
		{
			$result = array('type' => 'string');
		}
		elseif (strpos($type, 'blob') !== FALSE)
		{
			$result = array('type' => 'binary');
		}
		elseif (strpos($type, 'real') !== FALSE
			OR strpos($type, 'floa') !== FALSE
			OR strpos($type, 'doub') !== FALSE)
		{
			$result = array('type' => 'float');
		}
		else
		{
			// Anything else is probably being used as intended by the standard
			return parent::datatype($type, $attribute);
		}

		if ($attribute !== NULL)
			return @$result[$attribute];

		return $result;
	}

	/**
	 * Quote a literal value for inclusion in a SQL query
	 *
	 * @uses Database_PDO::escape()
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string
	 */
	public function quote_literal($value)
	{
		if (is_object($value) OR is_string($value))
			return $this->escape($value);

		return parent::quote_literal($value);
	}
}
