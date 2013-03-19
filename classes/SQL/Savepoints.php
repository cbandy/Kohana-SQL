<?php
namespace SQL;

/**
 * SQL:1999
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Savepoints extends Transactions
{
	/**
	 * Destroy a savepoint in the current transaction.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Savepoint name
	 * @return  void
	 */
	public function release($name);

	/**
	 * Revert to a savepoint in the current transaction.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Savepoint name
	 * @return  void
	 */
	public function rollback_to($name);

	/**
	 * Set a savepoint in the current transaction.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Savepoint name
	 * @return  void
	 */
	public function savepoint($name);
}
