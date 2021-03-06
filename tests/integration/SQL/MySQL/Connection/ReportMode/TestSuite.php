<?php
namespace SQL\MySQL;

require_once __DIR__.'/../ConnectionTest.php';
require_once __DIR__.'/../CommandTest.php';
require_once __DIR__.'/../QueryTest.php';
require_once __DIR__.'/../ConstraintViolationTest.php';

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
abstract class Connection_ReportMode_TestSuite extends \PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$class = get_called_class();
		$suite = new $class;
		$suite->addTestSuite('SQL\MySQL\Connection_ConnectionTest');
		$suite->addTestSuite('SQL\MySQL\Connection_CommandTest');
		$suite->addTestSuite('SQL\MySQL\Connection_QueryTest');
		$suite->addTestSuite('SQL\MySQL\Connection_ConstraintViolationTest');

		return $suite;
	}

	/**
	 * @var integer Backup of mysqli report setting
	 */
	protected $report_mode;

	public function setup()
	{
		if ( ! extension_loaded('mysqli'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('MySQLi extension not installed');

		$driver = new \mysqli_driver;
		$this->report_mode = $driver->report_mode;
	}

	public function teardown()
	{
		mysqli_report($this->report_mode);
	}
}
