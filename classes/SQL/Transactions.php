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
	/**
	 * Commit the current transaction.
	 *
	 * @throws  RuntimeException
	 * @return  void
	 */
	public function commit();

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
