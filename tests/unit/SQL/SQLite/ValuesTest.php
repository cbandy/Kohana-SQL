<?php
namespace SQL\SQLite;

/**
 * @package     SQL
 * @subpackage  SQLite
 * @author      Chris Bandy
 */
class ValuesTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\SQLite\Values::__toString
	 */
	public function test_toString()
	{
		$values = new Values;

		$values->row(array('a'));
		$this->assertSame('SELECT ?', (string) $values);

		$values->row(array('b'));
		$this->assertSame('SELECT ? UNION ALL SELECT ?', (string) $values);

		$values->row(array('c'));
		$this->assertSame(
			'SELECT ? UNION ALL SELECT ? UNION ALL SELECT ?', (string) $values
		);
	}
}
