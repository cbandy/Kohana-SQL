<?php
namespace SQL\SQLite\DML;

use SQL\DML\Select as SQL_Select;

/**
 * SELECT statement for SQLite. Allows OFFSET without LIMIT.
 *
 * @package     SQL
 * @subpackage  SQLite
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_select.html
 * @link http://www.sqlite.org/limits.html
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

		if ($this->offset)
		{
			$value .= ' LIMIT :offset,';

			if ($this->limit !== NULL)
			{
				$value .= ':limit';
			}
			else
			{
				// The maximum value of bigint
				$value .= '9223372036854775807';
			}
		}
		elseif ($this->limit !== NULL)
		{
			$value .= ' LIMIT :limit';
		}

		return $value;
	}
}
