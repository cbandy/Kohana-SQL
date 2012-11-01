<?php
namespace SQL\PDO;

use PDO;
use PDOException;
use SQL\Connection as SQL_Connection;
use SQL\RuntimeException;

/**
 * An SQL\Connection using PDO.
 *
 *  Configuration | Type    | Description
 *  ------------- | ----    | -----------
 *  dsn           | string  | Full DSN or a predefined DSN name
 *  options       | array   | Driver-specific options
 *  password      | string  |
 *  persistent    | boolean | Use the PHP connection pool
 *  username      | string  |
 *
 * *[DSN]: Data Source Name
 * *[PDO]: PHP Data Objects
 * *[URI]: Uniform Resource Identifier
 *
 * @link http://www.php.net/manual/book.pdo
 * @link http://www.php.net/manual/pdo.construct PDO connection parameters
 *
 * @package     SQL
 * @subpackage  PDO
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Connection extends SQL_Connection
{
	/**
	 * @var PDO
	 */
	protected $connection;

	/**
	 * @var array   Configuration options
	 */
	protected $config;

	/**
	 * @param   array   $config Configuration
	 * @param   string  $name   Connection name
	 */
	public function __construct($config, $name = NULL)
	{
		if ($name === NULL)
		{
			$name = 'kohana_cxn_'.sha1($config['dsn']);
		}

		parent::__construct($name);

		$this->config = $config;

		// Use exceptions for all errors
		$this->config['options'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		if ( ! isset($this->config['password']))
		{
			$this->config['password'] = NULL;
		}

		if ( ! empty($this->config['persistent']))
		{
			$this->config['options'][PDO::ATTR_PERSISTENT] = TRUE;
		}

		if ( ! isset($this->config['username']))
		{
			$this->config['username'] = NULL;
		}
	}

	public function connect()
	{
		try
		{
			$this->connection = new PDO(
				$this->config['dsn'],
				$this->config['username'],
				$this->config['password'],
				$this->config['options']
			);
		}
		catch (PDOException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}
	}

	public function disconnect()
	{
		$this->connection = NULL;
	}

	public function execute_command($statement)
	{
		$this->connection OR $this->connect();

		if ( ! is_string($statement))
		{
			$parameters = $statement->parameters();
			$statement = (string) $statement;
		}

		if (empty($statement))
			return 0;

		try
		{
			if (empty($parameters))
			{
				$result = $this->connection->exec($statement);
			}
			else
			{
				$result = $this->connection->prepare($statement);
				$result->execute($parameters);
				$result = $result->rowCount();
			}
		}
		catch (PDOException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		return $result;
	}

	public function execute_query($statement)
	{
		$this->connection OR $this->connect();

		if ( ! is_string($statement))
		{
			$parameters = $statement->parameters();
			$statement = (string) $statement;
		}

		if (empty($statement))
			return NULL;

		try
		{
			if (empty($parameters))
			{
				$statement = $this->connection->query($statement);
			}
			else
			{
				$statement = $this->connection->prepare($statement);
				$statement->execute($parameters);
			}
		}
		catch (PDOException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		if ($statement->columnCount() === 0)
			return NULL;

		return new Result_Seekable($statement);
	}
}
