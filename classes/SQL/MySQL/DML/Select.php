<?php
namespace SQL\MySQL\DML;

use SQL\DML\Select as SQL_Select;

/**
 * SELECT statement for MySQL. Allows OFFSET without LIMIT, and automatically
 * uses the DUAL table when necessary.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/select.html
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
		elseif ($this->where)
		{
			$value .= ' FROM DUAL';
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
				// The maximum value of bigint unsigned
				$value .= '18446744073709551615';
			}
		}
		elseif ($this->limit !== NULL)
		{
			$value .= ' LIMIT :limit';
		}

		return $value;
	}
}
