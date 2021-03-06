<?php
namespace SQL\PDO;

require_once __DIR__.'/TestCase.php';

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Execution_SQLiteTest extends Execution_TestCase
{
	public static function setupbeforeclass()
	{
		parent::setupbeforeclass();

		if ( ! extension_loaded('pdo_sqlite'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO SQLite extension not installed');

		if (empty($_SERVER['SQLITE']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for SQLite');
	}

	public function setup()
	{
		$config = json_decode($_SERVER['SQLITE'], TRUE);

		$this->connection = new \PDO($config['dsn']);
		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}
}
