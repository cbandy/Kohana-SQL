<?php
namespace SQL\PDO;

require_once __DIR__.'/TestCase.php';

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Execution_MySQLTest extends Execution_TestCase
{
	public function setup()
	{
		$config = json_decode($_SERVER['MYSQL'], TRUE);

		$this->connection = new \PDO($config['dsn'], $config['username']);
		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}
}
