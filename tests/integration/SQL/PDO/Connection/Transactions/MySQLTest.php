<?php
namespace SQL\PDO;

require_once __DIR__.'/TestCase.php';

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Connection_Transactions_MySQLTest extends Connection_Transactions_TestCase
{
	public static function setupbeforeclass()
	{
		parent::setupbeforeclass();

		if ( ! extension_loaded('pdo_mysql'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO MySQL extension not installed');

		if (empty($_SERVER['MYSQL']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for MySQL');
	}

	protected function connection()
	{
		$config = json_decode($_SERVER['MYSQL'], TRUE);

		return new Connection($config);
	}
}
