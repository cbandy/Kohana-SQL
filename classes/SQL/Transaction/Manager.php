<?php
namespace SQL;

/**
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Transaction_Manager implements Transactions
{
	/**
	 * @var integer Number of markers generated this transaction
	 */
	protected $markers = 0;

	/**
	 * @var string  Marker for the transaction
	 */
	protected $transaction;

	/**
	 * @param   Savepoints  $connection
	 */
	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	/**
	 * Commit the current transaction or release a savepoint. The marker and
	 * those created after it are destroyed.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Marker name or NULL to commit the transaction
	 * @return  void
	 */
	public function commit($name = NULL)
	{
		if ($name === NULL OR $name === $this->transaction)
		{
			$this->connection->commit();
			$this->reset();
		}
		else
		{
			$this->connection->release($name);
		}
	}

	protected function generate_name()
	{
		return 'kohana_txn_'.++$this->markers;
	}

	protected function reset()
	{
		$this->markers = 0;
		$this->transaction = NULL;
	}

	/**
	 * Abort the current transaction or revert a savepoint. The marker is
	 * destroyed.
	 *
	 * @throws  RuntimeException
	 * @param   string  $name   Marker name or NULL to abort the transaction
	 * @return  void
	 */
	public function rollback($name = NULL)
	{
		if ($name === NULL OR $name === $this->transaction)
		{
			$this->connection->rollback();
			$this->reset();
		}
		else
		{
			$this->connection->rollback_to($name);
			$this->connection->release($name);
		}
	}

	/**
	 * Start a transaction or set a savepoint in the current transaction.
	 *
	 * @throws  RuntimeException
	 * @return  string  Marker name
	 */
	public function start()
	{
		$name = $this->generate_name();

		if ($this->transaction === NULL)
		{
			$this->connection->start();
			$this->transaction = $name;
		}
		else
		{
			$this->connection->savepoint($name);
		}

		return $name;
	}
}
