<?php
namespace SQL;

/**
 * Interface for a connection to an SQL server.
 *
 * @package SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Connection implements Executor
{
	/**
	 * @var string  Connection name
	 */
	protected $name;

	/**
	 * @param   string  $name   Connection name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function __toString()
	{
		return $this->name;
	}

	/**
	 * Connect.
	 *
	 * @throws  RuntimeException
	 * @return  void
	 */
	abstract public function connect();

	/**
	 * Disconnect.
	 *
	 * @return  void
	 */
	abstract public function disconnect();
}
