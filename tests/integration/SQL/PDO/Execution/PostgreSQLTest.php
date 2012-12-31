<?php
namespace SQL\PDO;

require_once __DIR__.'/TestCase.php';

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Execution_PostgreSQLTest extends Execution_TestCase
{
	public static function setupbeforeclass()
	{
		parent::setupbeforeclass();

		if ( ! extension_loaded('pdo_pgsql'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO PostgreSQL extension not installed');

		if (empty($_SERVER['POSTGRESQL']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for PostgreSQL');
	}

	public function setup()
	{
		$config = json_decode($_SERVER['POSTGRESQL'], TRUE);

		$this->connection = new \PDO($config['dsn']);
		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}
}
