<?php
namespace SQL\PDO;

require_once __DIR__.'/TestCase.php';

use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Connection_Transactions_SQLiteTest extends Connection_Transactions_TestCase
{
	public static function setupbeforeclass()
	{
		parent::setupbeforeclass();

		if ( ! extension_loaded('pdo_sqlite'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO SQLite extension not installed');

		if (empty($_SERVER['SQLITE']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for SQLite');
	}

	protected function connection()
	{
		$config = json_decode($_SERVER['SQLITE'], TRUE);

		return new Connection($config);
	}

	protected function truncate($table)
	{
		$truncate = new Statement(
			'DELETE FROM '.$table->getTableMetaData()->getTableName()
		);

		return array($truncate);
	}
}
