<?php
namespace SQL;

/**
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Transactions
{
	const READ_UNCOMMITTED  = 'READ UNCOMMITTED';
	const READ_COMMITTED    = 'READ COMMITTED';
	const REPEATABLE_READ   = 'REPEATABLE READ';
	const SERIALIZABLE      = 'SERIALIZABLE';

	/**
	 * Start a transaction with the current isolation level or set a savepoint
	 * in the current transaction.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Savepoint name or NULL to have one generated
	 * @return  string  Savepoint name
	 */
	public function begin($name = NULL);

	/**
	 * Commit the current transaction or release a savepoint.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Savepoint name or NULL to commit the transaction
	 * @return  void
	 */
	public function commit($name = NULL);

	/**
	 * Abort the current transaction or revert to a savepoint.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Savepoint name or NULL to abort the transaction
	 * @return  void
	 */
	public function rollback($name = NULL);

	/**
	 * Set a savepoint in the current transaction.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Savepoint name or NULL to have one generated
	 * @return  string  Savepoint name
	 */
	public function savepoint($name = NULL);

	/**
	 * Start a transaction, optionally setting the isolation level.
	 *
	 * @throws  RuntimeException
	 * @param   string  $level  Isolation level
	 * @return  string  Transaction name
	 */
	public function start_transaction($level = NULL);
}
