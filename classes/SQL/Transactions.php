<?php
namespace SQL;

/**
 * SQL-92
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012-2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Transactions
{
	const READ_UNCOMMITTED  = 'READ UNCOMMITTED';
	const READ_COMMITTED    = 'READ COMMITTED';
	const REPEATABLE_READ   = 'REPEATABLE READ';
	const SERIALIZABLE      = 'SERIALIZABLE';

	/**
	 * Commit the current transaction.
	 *
	 * @throws  RuntimeException
	 * @return  void
	 */
	public function commit();

	/**
	 * Set the isolation level for subsequent transactions.
	 *
	 * @throws  RuntimeException
	 * @param   string  $level  Isolation level
	 * @return  void
	 */
	public function isolation_level($level);
	// MySQL: SET SESSION TRANSACTION ISOLATION LEVEL {level}; http://dev.mysql.com/doc/en/set-transaction.html
	// PostgreSQL: SET SESSION CHARACTERISTICS AS TRANSACTION {level}; http://www.postgresql.org/docs/current/static/sql-set-transaction.html
	// SQL Server: SET TRANSACTION ISOLATION LEVEL {level}; http://msdn.microsoft.com/en-us/library/ms173763.aspx
	// SQLite: PRAGMA read_uncommitted = 1; http://www.sqlite.org/pragma.html#pragma_read_uncommitted

	/**
	 * Abort the current transaction.
	 *
	 * @throws  RuntimeException
	 * @return  void
	 */
	public function rollback();

	/**
	 * Start a transaction.
	 *
	 * @throws  RuntimeException
	 * @return  void
	 */
	public function start();
}
