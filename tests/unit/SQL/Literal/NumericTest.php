<?php
namespace SQL\Literal;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class NumericTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\Literal\Numeric::__construct
	 */
	public function test_constructor()
	{
		$value = new \stdClass;
		$scale = new \stdClass;
		$numeric = new Numeric($value, $scale);

		$this->assertSame($value, $numeric->value);
		$this->assertSame($scale, $numeric->scale);
	}
}
