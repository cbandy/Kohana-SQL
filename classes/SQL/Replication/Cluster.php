<?php
namespace SQL;

abstract class Replication_Cluster
{
	/**
	 * Any available connection.
	 *
	 * @return  Executor
	 */
	abstract public function any();

	/**
	 * Execute an SQL statement on the master connection.
	 *
	 * @throws  RuntimeException
	 * @param   string|Statement    $statement  SQL query
	 * @return  Result  Result set or NULL
	 */
	public function execute_query_strict($statement)
	{
		return $this->master()->execute_query($statement);
	}

	/**
	 * Execute a read-only SQL statement on any available connection.
	 *
	 * @throws  RuntimeException
	 * @param   string|Statement    $statement  SQL query
	 * @return  Result  Result set or NULL
	 */
	public function execute_query_tolerant($statement)
	{
		return $this->any()->execute_query($statement);
	}

	/**
	 * Execute a read-only SQL statement on any connection that has received
	 * the specified event.
	 *
	 * @see replication_event()
	 *
	 * @throws  RuntimeException
	 * @param   string|Statement    $statement  SQL query
	 * @param   string              $event      Replication event
	 * @return  Result  Result set or NULL
	 */
	public function execute_query_tolerant_of_event($statement, $event)
	{
		return $this->tolerant_of_event($event)->execute_query($statement);
	}

	/**
	 * Execute a read-only SQL statement on any connection that is behind master
	 * less than the specified delay.
	 *
	 * @throws  RuntimeException
	 * @param   string|Statement    $statement  SQL query
	 * @param   integer             $delay      FIXME seconds? milliseconds?
	 * @return  Result  Result set or NULL
	 */
	public function execute_query_tolerant_of_time($statement, $delay)
	{
		return $this->tolerant_of_time($delay)->execute_query($statement);
	}

	/**
	 * The master connection.
	 *
	 * @return  Executor
	 */
	abstract public function master();

	/**
	 * Read the current replication position of the master connection.
	 *
	 * @see tolerant_of_event()
	 *
	 * @throws  RuntimeException
	 * @return  string  Replication event
	 */
	abstract public function replication_event();

	/**
	 * Find a connection that has received the specified event.
	 *
	 * @see replication_event()
	 *
	 * @param   string  $event  Replication event
	 * @return  Executor
	 */
	abstract public function tolerant_of_event($event);

	/**
	 * Find a connection that is behind master less than the specified delay.
	 *
	 * @param   integer $delay  FIXME seconds? milliseconds?
	 * @return  Executor
	 */
	abstract public function tolerant_of_time($delay);
}
