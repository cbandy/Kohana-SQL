<?php
namespace SQL\PostgreSQL\DML;

use SQL\Column;
use SQL\DML\Select as SQL_Select;
use SQL\Expression;
use SQL\Identifier;

/**
 * SELECT statement for PostgreSQL. Allows the criteria for DISTINCT rows to be set.
 *
 * @package     SQL
 * @subpackage  PostgreSQL
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-select.html
 */
class Select extends SQL_Select
{
	public function __construct($columns = NULL)
	{
		parent::__construct($columns);

		$this->distinct =& $this->parameters[':distinct'];
	}

	public function __toString()
	{
		$value = 'SELECT';

		if ($this->distinct)
		{
			$value .= is_bool($this->distinct)
				? ' DISTINCT'
				: ' DISTINCT ON (:distinct)';
		}

		$value .= $this->values ? ' :values' : ' *';

		if ($this->from)
		{
			$value .= ' FROM :from';
		}

		if ($this->where)
		{
			$value .= ' WHERE :where';
		}

		if ($this->group_by)
		{
			$value .= ' GROUP BY :groupby';
		}

		if ($this->having)
		{
			$value .= ' HAVING :having';
		}

		if ($this->order_by)
		{
			$value .= ' ORDER BY :orderby';
		}

		if ($this->limit !== NULL)
		{
			$value .= ' LIMIT :limit';
		}

		if ($this->offset)
		{
			$value .= ' OFFSET :offset';
		}

		return $value;
	}

	/**
	 * Append multiple columns and/or expressions by which rows should be
	 * considered unique.
	 *
	 * @link http://www.postgresql.org/docs/current/static/sql-select.html#SQL-DISTINCT
	 *
	 * @param   array|boolean   $columns    List of columns, each converted to Column; TRUE for the entire row or NULL or FALSE to reset
	 * @return  $this
	 */
	public function distinct($columns = TRUE)
	{
		if ( ! is_array($columns))
			return parent::distinct($columns);

		$this->distinct = NULL;

		foreach ($columns as $column)
		{
			if ( ! $column instanceof Expression
				AND ! $column instanceof Identifier)
			{
				$column = new Column($column);
			}

			$this->distinct[] = $column;
		}

		return $this;
	}
}
