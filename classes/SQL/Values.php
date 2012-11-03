<?php
namespace SQL;

/**
 * Expression for building one or more row values. Feature F641 of SQL:1999.
 *
 * [!!] MySQL and SQLite only allow this in INSERT statements
 * [!!] Older versions of SQLite allow only one row with this syntax
 *
 * @package     SQL
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/insert.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-values.html PostgreSQL
 * @link http://www.sqlite.org/lang_insert.html SQLite
 * @link http://msdn.microsoft.com/library/dd776382.aspx Transact-SQL
 *
 * @see SQL\SQLite\Values
 */
class Values extends Expression
{
	/**
	 * @var array   Rows of values
	 */
	public $rows;

	/**
	 * @uses row()
	 *
	 * @param   array   $values Row of values
	 */
	public function __construct($values = NULL)
	{
		$this->rows =& $this->parameters;

		$this->row($values);
	}

	public function __toString()
	{
		$count = count($this->rows);
		$value = 'VALUES (?)';

		if ($count > 1)
		{
			$value .= str_repeat(', (?)', $count - 1);
		}

		return $value;
	}

	/**
	 * Append one row or expression.
	 *
	 * @param   array   Row of values or NULL to reset
	 * @return  $this
	 */
	public function row($values)
	{
		if ($values === NULL)
		{
			$this->rows = array();
		}
		else
		{
			$this->rows[] = $values;
		}

		return $this;
	}

	/**
	 * Append multiple rows or expressions.
	 *
	 * @param   array   List of rows or NULL to reset
	 * @return  $this
	 */
	public function rows($rows)
	{
		if ($rows === NULL)
		{
			$this->rows = array();
		}
		else
		{
			foreach ($rows as $row)
			{
				$this->rows[] = $row;
			}
		}

		return $this;
	}
}
