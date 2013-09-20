<?php
namespace SQL\MySQL;

use SQL\Result as SQL_Result;
use SQL\Result\Types;

/**
 * Forward-only result set for a MySQLi result.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/class.mysqli-result
 */
class Result extends SQL_Result implements Types
{
	public static $types = array(
		MYSQLI_TYPE_DECIMAL => 'decimal',
		MYSQLI_TYPE_NEWDECIMAL => 'numeric',
		MYSQLI_TYPE_BIT => 'bit',
		MYSQLI_TYPE_TINY => 'tinyint',
		MYSQLI_TYPE_SHORT => 'smallint',
		MYSQLI_TYPE_LONG => 'int',
		MYSQLI_TYPE_FLOAT => 'float',
		MYSQLI_TYPE_DOUBLE => 'double',
		MYSQLI_TYPE_NULL => 'null',
		MYSQLI_TYPE_TIMESTAMP => 'timestamp',
		MYSQLI_TYPE_LONGLONG => 'bigint',
		MYSQLI_TYPE_INT24 => 'mediumint',
		MYSQLI_TYPE_DATE => 'date',
		MYSQLI_TYPE_TIME => 'time',
		MYSQLI_TYPE_DATETIME => 'datetime',
		MYSQLI_TYPE_YEAR => 'year',
		MYSQLI_TYPE_NEWDATE => 'date',
		MYSQLI_TYPE_INTERVAL => 'interval',
		MYSQLI_TYPE_ENUM => 'enum',
		MYSQLI_TYPE_SET => 'set',
		MYSQLI_TYPE_TINY_BLOB => 'tinyblob',
		MYSQLI_TYPE_MEDIUM_BLOB => 'mediumblob',
		MYSQLI_TYPE_LONG_BLOB => 'longblob',
		MYSQLI_TYPE_BLOB => 'blob',
		MYSQLI_TYPE_VAR_STRING => 'varchar',
		MYSQLI_TYPE_STRING => 'char',
		MYSQLI_TYPE_CHAR => 'tinyint',
		MYSQLI_TYPE_GEOMETRY => 'geometry',
	);

	/**
	 * @var array
	 */
	protected $current_row;

	/**
	 * @var array
	 */
	protected $fields;

	/**
	 * @var mysqli_result
	 */
	protected $result;

	/**
	 * @param   mysqli_result   $result
	 */
	public function __construct($result)
	{
		$this->result = $result;
		$this->fetch();
	}

	public function __destruct()
	{
		$this->result->free();
	}

	public function current()
	{
		return $this->current_row;
	}

	protected function fetch()
	{
		$this->current_row = $this->result->fetch_assoc();
	}

	protected function fetch_fields()
	{
		while ($field = $this->result->fetch_field())
		{
			$this->fields[$field->name] = $field;
		}
	}

	public function next()
	{
		$this->fetch();
		return parent::next();
	}

	public function type($name = NULL)
	{
		$this->fields OR $this->fetch_fields();

		if ($name === NULL)
		{
			$field = reset($this->fields);
		}
		elseif (isset($this->fields[$name]))
		{
			$field = $this->fields[$name];
		}
		else
		{
			return NULL;
		}

		return static::$types[$field->type];
	}

	public function valid()
	{
		return ($this->current_row !== NULL);
	}
}
