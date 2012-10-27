<?php
namespace SQL;

/**
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Executor
{
	/**
	 * Execute an SQL statement, returning the number of rows affected.
	 *
	 * Do not use this method to count the rows returned by a query (e.g., a
	 * SELECT statement). Always use execute_query() for statements that return
	 * results.
	 *
	 * @throws  RuntimeException
	 * @param   string|Statement    $statement  SQL command
	 * @return  integer Number of affected rows
	 */
	public function execute_command($statement);

	/**
	 * Execute an SQL statement, returning the result set or NULL when the
	 * statement is not a query (e.g., a DELETE statement).
	 *
	 * @throws  RuntimeException
	 * @param   string|Statement    $statement  SQL query
	 * @return  Result  Result set or NULL
	 */
	public function execute_query($statement);
}
