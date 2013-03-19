<?php
namespace SQL\PDO;

require_once __DIR__.'/TestCase.php';

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Connection_Transactions_PostgreSQLTest extends Connection_Transactions_TestCase
{
	public static function setupbeforeclass()
	{
		parent::setupbeforeclass();

		if ( ! extension_loaded('pdo_pgsql'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO PostgreSQL extension not installed');

		if (empty($_SERVER['POSTGRESQL']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for PostgreSQL');
	}

	protected function connection()
	{
		$config = json_decode($_SERVER['POSTGRESQL'], TRUE);

		return new Connection($config);
	}
}
