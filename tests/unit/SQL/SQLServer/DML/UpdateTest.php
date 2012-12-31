<?php
namespace SQL\SQLServer\DML;

/**
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 */
class UpdateTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\SQLServer\DML\Update::__toString
	 */
	public function test_toString()
	{
		$update = new Update;
		$update
			->table('a')
			->set(array('b' => 0))
			->from('c')
			->where('d', '=', 1)
			->limit(2);

		$this->assertSame(
			'UPDATE TOP (:limit) :table SET :values FROM :from WHERE :where',
			(string) $update
		);
	}
}
