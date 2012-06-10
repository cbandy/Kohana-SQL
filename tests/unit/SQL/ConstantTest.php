<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ConstantTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\Constant::__construct
	 */
	public function test_constructor()
	{
		$value = new \stdClass;
		$constant = new Constant($value);

		$this->assertSame($value, $constant->value);
	}
}
