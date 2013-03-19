<?php
namespace SQL\Transaction;

/**
 * SQL:1999
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/set-transaction.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-set-transaction.html PostgreSQL
 * @link http://msdn.microsoft.com/en-us/library/ms173763.aspx SQL Server
 * @link http://www.sqlite.org/pragma.html#pragma_read_uncommitted SQLite
 */
interface Isolation
{
	const READ_UNCOMMITTED  = 'READ UNCOMMITTED';
	const READ_COMMITTED    = 'READ COMMITTED';
	const REPEATABLE_READ   = 'REPEATABLE READ';
	const SERIALIZABLE      = 'SERIALIZABLE';

	/**
	 * Set the isolation level for subsequent transactions.
	 *
	 * @throws  RuntimeException
	 * @param   string  $level  Isolation level
	 * @return  void
	 */
	public function isolation_level($level);
	// MySQL: SET SESSION TRANSACTION ISOLATION LEVEL {level};
	// Oracle: ALTER SESSION SET ISOLATION_LEVEL = {level};
	// PostgreSQL: SET SESSION CHARACTERISTICS AS TRANSACTION {level};
	// SQL Server: SET TRANSACTION ISOLATION LEVEL {level};
	// SQLite: PRAGMA read_uncommitted = 1;
}
