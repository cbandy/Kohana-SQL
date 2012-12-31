<?php
namespace SQL\SQLServer\DML;

use SQL\DML\Update as SQL_Update;

/**
 * UPDATE statement for SQL Server.
 *
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/library/ms177523.aspx
 */
class Update extends SQL_Update
{
	public function __toString()
	{
		$value = 'UPDATE';

		if ($this->limit !== NULL)
		{
			$value .= ' TOP (:limit)';
		}

		$value .= ' :table SET :values';

		if ($this->from)
		{
			$value .= ' FROM :from';
		}

		if ($this->where)
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}
}
