<?php
namespace SQL\PDO;

require_once __DIR__.'/TestCase.php';

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Connection_SQLiteTest extends Connection_TestCase
{
	public static function setupbeforeclass()
	{
		parent::setupbeforeclass();

		if ( ! extension_loaded('pdo_sqlite'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PDO SQLite extension not installed');
	}

	protected function expect_syntax_error_exception()
	{
		$this->setExpectedException('SQL\RuntimeException', 'syntax', 'HY000');
	}

	public function setup()
	{
		$config = json_decode($_SERVER['SQLITE'], TRUE);

		$this->connection = new Connection($config);
	}
}
