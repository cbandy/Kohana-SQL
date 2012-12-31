<?php
namespace SQL\SQLServer\DML;

use SQL\DML\Select as SQL_Select;

/**
 * SELECT statement for SQL Server.
 *
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/library/ms189499.aspx
 */
class Select extends SQL_Select
{
	public function __toString()
	{
		$value = 'SELECT';

		if ($this->distinct)
		{
			$value .= ' DISTINCT';
		}

		if ($this->limit !== NULL AND ! $this->offset)
		{
			$value .= ' TOP (:limit)';
		}

		$value .= $this->values ? ' :values' : ' *';

		if ($this->offset)
		{
			$row_number = 'kohana_row_number';
			$value .= ', ROW_NUMBER() OVER(ORDER BY :orderby) AS '.$row_number;
		}

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

		if ($this->offset)
		{
			$table = 'kohana_'.sha1($value.','.serialize($this->parameters));
			$table_row_number = $table.'.'.$row_number;

			// Using a CTE here would prevent this query from being a subquery
			$value = 'SELECT * FROM ('.$value.') AS '.$table
				.' WHERE '.$table_row_number.' > :offset';

			if ($this->limit !== NULL)
			{
				$value .= ' AND '.$table_row_number.' <= (:offset + :limit)';
			}
		}
		elseif ($this->order_by)
		{
			$value .= ' ORDER BY :orderby';
		}

		return $value;
	}
}
