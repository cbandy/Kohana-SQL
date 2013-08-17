<?php
namespace SQL\MySQL;

require_once __DIR__.'/TestSuite.php';

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Connection_ReportMode_StrictTest extends Connection_ReportMode_TestSuite
{
	public function setup()
	{
		parent::setup();

		mysqli_report(MYSQLI_REPORT_STRICT);
	}
}
