<?php
namespace SQL\PostgreSQL;

use SQL\Result_Seekable;
use SQL\Result\Types;

/**
 * Seekable result set for a PostgreSQL resource.
 *
 * @package     SQL
 * @subpackage  PostgreSQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/pgsql.resources
 */
class Result extends Result_Seekable implements Types
{
	/**
	 * @var resource    From pg_query() or pg_get_result()
	 */
	protected $result;

	/**
	 * @param   resource    $result From pg_query() or pg_get_result()
	 */
	public function __construct($result)
	{
		parent::__construct(pg_num_rows($result));

		$this->result = $result;
	}

	public function __destruct()
	{
		pg_free_result($this->result);
	}

	public function current()
	{
		return $this->fetch($this->position);
	}

	/**
	 * Retrieve a specific row without moving the pointer.
	 *
	 * Raises E_WARNING and returns FALSE when $position is invalid.
	 *
	 * @param   integer $position
	 * @return  array|FALSE
	 */
	public function fetch($position)
	{
		return pg_fetch_assoc($this->result, $position);
	}

	public function get($name = NULL, $default = NULL)
	{
		if ($this->valid()
			AND ($name === NULL OR ($name = pg_field_num($this->result, $name)) >= 0)
			AND ($result = pg_fetch_result($this->result, $this->position, $name)) !== NULL)
		{
			// Field exists and is not NULL
			return $result;
		}

		return $default;
	}

	public function offsetGet($offset)
	{
		if ( ! $this->offsetExists($offset))
			return NULL;

		return $this->fetch($offset);
	}

	public function to_array($key = NULL, $value = NULL)
	{
		if ($this->count === 0)
			return array();

		if ($key !== NULL)
			return parent::to_array($key, $value);

		// Indexed rows
		if ($value === NULL)
			return pg_fetch_all($this->result);

		// Indexed columns
		return pg_fetch_all_columns(
			$this->result, pg_field_num($this->result, $value)
		);
	}

	public function type($name = NULL)
	{
		if ($name === NULL OR ($name = pg_field_num($this->result, $name)) >= 0)
			return pg_field_type($this->result, $name);

		return NULL;
	}
}
