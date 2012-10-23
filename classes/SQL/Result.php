<?php
namespace SQL;

/**
 * Forward-only result set iterator. A single column can be retrieved with
 * [Result::get], while [Result::to_array] retrieves one or more columns from
 * multiple rows at once.
 *
 *     $result = $connection->execute($query);
 *
 *     foreach ($result as $row)
 *     {
 *         $library->do_something($row['id'], $row['name']);
 *     }
 *
 * @package     SQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Result implements \Iterator
{
	/**
	 * @var integer Current row index
	 */
	protected $position = 0;

	/**
	 * Return the current row without validating the current position.
	 * Implements [Iterator::current].
	 *
	 * @return  array
	 */
	abstract public function current();

	/**
	 * Return a column from the current row.
	 *
	 *     // single column
	 *     $name = $result->get('name');
	 *
	 *     // first column
	 *     $count = $connection->execute_query('SELECT COUNT(*) FROM t')->get();
	 *
	 * @param   string  $name       Column name or NULL to return the first
	 * @param   mixed   $default    Default value if the column is NULL
	 * @return  mixed
	 */
	public function get($name = NULL, $default = NULL)
	{
		if ($this->valid())
		{
			$row = $this->current();

			if ($name === NULL)
			{
				$result = reset($row);

				if ($result !== NULL)
					return $result;
			}
			else
			{
				if (isset($row[$name]))
					return $row[$name];
			}
		}

		return $default;
	}

	/**
	 * The offset of the row that will be returned by the next call to
	 * current(). Implements [Iterator::key].
	 *
	 * @return integer
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Move the current position to the next row. Implements [Iterator::next].
	 *
	 * @return  $this
	 */
	public function next()
	{
		++$this->position;
		return $this;
	}

	/**
	 * Do nothing. Implements [Iterator::rewind].
	 *
	 * @return  $this
	 */
	public function rewind()
	{
		return $this;
	}

	/**
	 * Return all of the remaining rows as an array.
	 *
	 *     // indexed array of rows
	 *     $rows = $result->to_array();
	 *
	 *     // indexed array of "name" values
	 *     $names = $result->to_array(NULL, 'name');
	 *
	 *     // associative array of rows by "id"
	 *     $rows = $result->to_array('id');
	 *
	 *     // associative array of "name" values by "id"
	 *     $names = $result->to_array('id', 'name');
	 *
	 * @param   string  $key    Column for associative keys
	 * @param   string  $value  Column for values
	 * @return  array
	 */
	public function to_array($key = NULL, $value = NULL)
	{
		$results = array();

		if ($key === NULL AND $value === NULL)
		{
			// Indexed rows
			foreach ($this as $row)
			{
				$results[] = $row;
			}
		}
		elseif ($key === NULL)
		{
			// Indexed columns
			foreach ($this as $row)
			{
				$results[] = $row[$value];
			}
		}
		elseif ($value === NULL)
		{
			// Associative rows
			foreach ($this as $row)
			{
				$results[$row[$key]] = $row;
			}
		}
		else
		{
			// Associative columns
			foreach ($this as $row)
			{
				$results[$row[$key]] = $row[$value];
			}
		}

		return $results;
	}

	/**
	 * Whether or not the next call to current() will succeed. Implements
	 * [Iterator::valid].
	 *
	 * @return  boolean
	 */
	abstract public function valid();
}
