<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array('any'),
			array('string'),
		);
	}

	/**
	 * @covers  SQL\Connection::__construct
	 * @covers  SQL\Connection::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   string  $name   Argument
	 */
	public function test_constructor($name)
	{
		$connection = $this->getMockBuilder('SQL\Connection')
			->setConstructorArgs(array($name))
			->getMockForAbstractClass();

		$this->assertSame($name, (string) $connection);
	}

	/**
	 * @covers  SQL\Connection::__destruct
	 */
	public function test_destructor()
	{
		$connection = $this->getMockBuilder('SQL\Connection')
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$connection->expects($this->once())->method('disconnect');

		$connection->__destruct();
	}
}
