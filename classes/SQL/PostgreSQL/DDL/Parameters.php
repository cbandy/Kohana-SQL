<?php
namespace SQL\PostgreSQL\DDL;

use SQL\Expression;

/**
 * Hash-like expression for PostgreSQL storage parameters.
 *
 * @package     SQL
 * @subpackage  PostgreSQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-createindex.html#SQL-CREATEINDEX-STORAGE-PARAMETERS
 * @link http://www.postgresql.org/docs/current/static/sql-createtable.html#SQL-CREATETABLE-STORAGE-PARAMETERS
 *
 * @property    array   $values Hash of parameters
 */
class Parameters extends Expression implements \ArrayAccess
{
	/**
	 * @var array   Hash of parameters
	 */
	protected $values;

	/**
	 * @param   array   $values Hash of (parameter => value) pairs
	 */
	public function __construct($values = array())
	{
		$this->values = $values;
		$this->parameters = array_values($this->values);
	}

	public function __get($name)
	{
		if ($name === 'values')
			return $this->values;
	}

	public function __set($name, $value)
	{
		if ($name === 'values')
		{
			$this->values = $value;
			$this->parameters = array_values($this->values);
		}
	}

	public function __toString()
	{
		$parameters = array_map(
			function ($parameter) { return $parameter.' = ?'; },
			array_keys($this->values)
		);

		return implode(', ', $parameters);
	}

	public function offsetExists($parameter)
	{
		return array_key_exists($parameter, $this->values);
	}

	public function offsetGet($parameter)
	{
		return $this->values[$parameter];
	}

	public function offsetSet($parameter, $value)
	{
		$this->values[$parameter] = $value;
		$this->parameters = array_values($this->values);
	}

	public function offsetUnset($parameter)
	{
		unset($this->values[$parameter]);
		$this->parameters = array_values($this->values);
	}
}
