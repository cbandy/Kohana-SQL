<?php
namespace SQL\PostgreSQL;

use Exception;
use SQL\Connection as SQL_Connection;
use SQL\RuntimeException;

/**
 * An SQL\Connection for [PostgreSQL](http://www.postgresql.org/)
 *
 *  Configuration | Type    | Description
 *  ------------- | ----    | -----------
 *  database      | string  |
 *  hostname      | string  | Server address or path to a local socket
 *  options       | string  | [PGOPTIONS][] parameter string
 *  password      | string  |
 *  persistent    | boolean | Use the PHP connection pool
 *  port          | integer | Server port
 *  ssl           | mixed   | TRUE to require, FALSE to disable, or 'prefer' to negotiate
 *  username      | string  |
 *
 * [PGOPTIONS]: http://www.postgresql.org/docs/current/static/runtime-config.html
 *
 * Instead of separate parameters, the full connection string can be configured
 * in `info` to be passed directly to `pg_connect()`.
 *
 * [!!] Set `--client_encoding` in `options` to use an encoding different than
 * the database default.
 *
 * @link http://www.php.net/manual/book.pgsql
 * @link http://www.postgresql.org/docs/current/static/libpq-connect.html Connection string definition
 *
 * @package     SQL
 * @subpackage  PostgreSQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Connection extends SQL_Connection
{
	/**
	 * Convert a configuration array into a connection string.
	 *
	 * @param   array   $array
	 * @return  string  Connection string
	 */
	public static function configuration($array)
	{
		extract($array);

		$info = '';

		if ( ! empty($hostname))
		{
			$info .= "host='".addcslashes($hostname, "'\\")."'";
		}

		if ( ! empty($port))
		{
			$info .= " port='".addcslashes($port, "'\\")."'";
		}

		if ( ! empty($username))
		{
			$info .= " user='".addcslashes($username, "'\\")."'";
		}

		if ( ! empty($password))
		{
			$info .= " password='".addcslashes($password, "'\\")."'";
		}

		if ( ! empty($database))
		{
			$info .= " dbname='".addcslashes($database, "'\\")."'";
		}

		if ( ! empty($options))
		{
			$info .= " options='".addcslashes($options, "'\\")."'";
		}

		if (isset($ssl))
		{
			if ($ssl === TRUE)
			{
				$info .= " sslmode='require'";
			}
			elseif ($ssl === FALSE)
			{
				$info .= " sslmode='disable'";
			}
			else
			{
				$info .= " sslmode='".addcslashes($ssl, "'\\")."'";
			}
		}

		return $info;
	}

	/**
	 * @throws  ErrorException
	 * @param   integer $number
	 * @param   string  $string
	 * @param   string  $file
	 * @param   integer $line
	 */
	public static function handle_error($number, $string, $file, $line)
	{
		throw new \ErrorException($string, $number, 0, $file, $line);
	}

	/**
	 * @var array   Configuration options
	 */
	protected $config;

	/**
	 * @var resource    Unique connection to the server
	 */
	protected $connection;

	/**
	 * @param   array   $config Configuration
	 * @param   string  $name   Connection name
	 */
	public function __construct($config, $name = NULL)
	{
		$this->config = $config;

		if ( ! isset($this->config['info']))
		{
			$this->config['info'] = $this->configuration($this->config);
		}

		if ($name === NULL)
		{
			$name = 'kohana_cxn_pg_'.sha1($this->config['info']);
		}

		parent::__construct($name);
	}

	public function connect()
	{
		set_error_handler(array($this, 'handle_error'));

		try
		{
			// Raises E_WARNING upon error
			$this->connection = empty($this->config['persistent'])
				? pg_connect($this->config['info'], PGSQL_CONNECT_FORCE_NEW)
				: pg_pconnect($this->config['info'], PGSQL_CONNECT_FORCE_NEW);
		}
		catch (Exception $e)
		{
			$error = new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		restore_error_handler();

		if (isset($error))
			throw $error;
	}

	public function disconnect()
	{
		if (is_resource($this->connection))
		{
			pg_close($this->connection);

			$this->connection = NULL;
		}
	}

	public function execute_command($statement)
	{
	}

	public function execute_query($statement)
	{
	}
}
