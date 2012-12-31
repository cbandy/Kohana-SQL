<?php
namespace SQL\SQLServer\DML;

/**
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\SQLServer\DML\Delete::__toString
	 */
	public function test_toString()
	{
		$delete = new Delete;
		$delete
			->from('a')
			->using('b')
			->where('c', '=', 'd')
			->limit(1);

		$this->assertSame(
			'DELETE TOP (:limit) FROM :table FROM :using WHERE :where',
			(string) $delete
		);
	}
}
