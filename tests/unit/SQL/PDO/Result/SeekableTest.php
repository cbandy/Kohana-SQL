<?php
namespace SQL\PDO;

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class Result_SeekableTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('pdo'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');
	}

	/**
	 * @covers  SQL\PDO\Result_Seekable::__construct
	 */
	public function test_constructor()
	{
		$statement = $this->getMock('PDOStatement', array('fetchAll'));
		$statement->expects($this->once())
			->method('fetchAll')
			->with($this->identicalTo(\PDO::FETCH_ASSOC));

		new Result_Seekable($statement);
	}
}
