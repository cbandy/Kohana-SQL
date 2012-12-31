<?php
namespace SQL\SQLServer\DML;

use SQL\DML\Delete as SQL_Delete;

/**
 * DELETE statement for SQL Server.
 *
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://msdn.microsoft.com/library/ms189835.aspx
 */
class Delete extends SQL_Delete
{
	public function __toString()
	{
		$value = 'DELETE';

		if ($this->limit !== NULL)
		{
			$value .= ' TOP (:limit)';
		}

		$value .= ' FROM :table';

		if ($this->using)
		{
			$value .= ' FROM :using';
		}

		if ($this->where)
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}
}
