<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Connection_ReportModeTest extends \PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new Connection_ReportModeTest;
		$suite->addTestSuite('SQL\MySQL\Connection_ConnectionTest');

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

		mysqli_report(MYSQLI_REPORT_ALL);
	}

	public function teardown()
	{
		mysqli_report($this->report_mode);
	}
}
