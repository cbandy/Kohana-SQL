<?php
namespace SQL\SQLite;

use SQL\Values as SQL_Values;

/**
 * Expression for building one or more row values for SQLite.
 *
 * @package     SQL
 * @subpackage  SQLite
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Values extends SQL_Values
{
	public function __toString()
	{
		$count = count($this->rows);

		return ($count > 0)
			? str_repeat('SELECT ? UNION ALL ', $count - 1).'SELECT ?'
			: '';
	}
}
