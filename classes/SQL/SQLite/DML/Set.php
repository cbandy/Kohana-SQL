<?php
namespace SQL\SQLite\DML;

use SQL\DML\Set as SQL_Set;

/**
 * @package     SQL
 * @subpackage  SQLite
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Set extends SQL_Set
{
	public function add($operator, $query)
	{
		if ( ! $this->empty)
		{
			$this->value .= ' '.strtoupper($operator).' ';
		}

		$this->empty = FALSE;
		$this->value .= '?';

		if ($query instanceof Select
			AND ($query->limit !== NULL OR $query->offset OR $query->order_by))
		{
			$select = new Select;
			$select->from($query);

			$this->parameters[] = $select;
		}
		else
		{
			$this->parameters[] = $query;
		}

		return $this;
	}
}
