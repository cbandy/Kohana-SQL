<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * Generic DROP TABLE statement.
 *
 * @package     SQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/drop-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-droptable.html PostgreSQL
 * @link http://www.sqlite.org/lang_droptable.html SQLite
 * @link http://msdn.microsoft.com/library/ms173790.aspx Transact-SQL
 */
class Drop_Table extends Drop
{
	/**
	 * @param   array|string|Expression|Identifier  $name       Converted to Table
	 * @param   boolean                             $cascade    Whether or not dependent objects should be dropped
	 */
	public function __construct($name = NULL, $cascade = NULL)
	{
		parent::__construct('TABLE', $name, $cascade);
	}

	/**
	 * Append the name of a table to be dropped.
	 *
	 * [!!] SQLite allows only one table per statement
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @return  $this
	 */
	public function name($table)
	{
		if ( ! $table instanceof Expression AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		$this->names[] = $table;

		return $this;
	}
}
