<?php
namespace SQL\MySQL;

use Exception;
use SQL\Connection as SQL_Connection;
use SQL\RuntimeException;

/**
 * SQL\Connection for [MySQL](http://www.mysql.com/)
 *
 * [!!] Requires MySQL >= 5.0.7
 *
 *  Configuration | Type    | Description
 *  ------------- | ----    | -----------
 *  database      | string  |
 *  flags         | integer | Combination of [client constants][], e.g. MYSQLI_CLIENT_COMPRESS
 *  hostname      | string  | Use `'127.0.0.1'` to [connect locally using TCP/IP][loopback]
 *  options       | array   | [Connect options][] as "key => value" pairs
 *  password      | string  |
 *  persistent    | boolean | Use the PHP connection pool
 *  port          | integer | Server port
 *  socket        | string  | Socket or named pipe. Hostname must be `NULL` or `'localhost'`
 *  username      | string  |
 *
 * [Client constants]: http://www.php.net/manual/mysqli.constants
 * [Connect options]:  http://www.php.net/manual/mysqli.options
 * [Loopback]:         http://dev.mysql.com/doc/en/can-not-connect-to-server.html
 *
 * [!!] Set `MYSQLI_SET_CHARSET_NAME` in `options` to use a [character set][]
 * different than the default.
 *
 * [Character set]: http://dev.mysql.com/doc/en/charset-charsets.html
 *
 * @link http://www.php.net/manual/book.mysqli
 *
 * @package     SQL
 * @subpackage  MySQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Connection extends SQL_Connection
{
	/**
	 * Convert a configuration array into connection settings.
	 *
	 * @param   array   $array
	 * @return  array   Connection settings
	 */
	public static function configuration($array)
	{
		extract($array);

		$result = array();

		$result['hostname'] = isset($hostname) ? $hostname : NULL;
		$result['port'] = isset($port) ? $port : NULL;
		$result['socket'] = isset($socket) ? $socket : NULL;
		$result['username'] = isset($username) ? $username : NULL;
		$result['password'] = isset($password) ? $password : NULL;
		$result['database'] = isset($database) ? $database : NULL;
		$result['flags'] = isset($flags) ? $flags : NULL;
		$result['options'] = empty($options) ? array() : $options;

		if ( ! empty($persistent))
		{
			$result['hostname'] = 'p:'.$result['hostname'];
		}

		return $result;
	}

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var mysqli
	 */
	protected $connection;

	/**
	 * @param   array   $config Configuration
	 * @param   string  $name   Connection name
	 */
	public function __construct($config, $name = NULL)
	{
		$this->config = $this->configuration($config);

		if ($name === NULL)
		{
			$name = 'kohana_cxn_mysql_'.sha1(serialize($this->config));
		}

		parent::__construct($name);
	}

	public function connect()
	{
		extract($this->config);

		$connection = mysqli_init();

		foreach ($options as $option => $value)
		{
			$connection->options($option, $value);
		}

		set_error_handler(function ($number, $string, $file, $line)
		{
			throw new \ErrorException($string, $number, 0, $file, $line);
		});

		try
		{
			// Raises E_WARNING upon some errors
			$result = $connection->real_connect(
				$hostname, $username, $password, $database, $port, $socket, $flags
			);
		}
		catch (Exception $e)
		{
			$error = new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		restore_error_handler();

		if (empty($result))
		{
			throw isset($error) ? $error
				: new RuntimeException("Unable to connect: $this");
		}

		$this->connection = $connection;
	}

	public function disconnect()
	{
		if ($this->connection)
		{
			$this->connection->close();
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
