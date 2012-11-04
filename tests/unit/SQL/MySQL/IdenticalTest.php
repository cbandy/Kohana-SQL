<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class IdenticalTest extends \PHPUnit_Framework_TestCase
{
	public function provider_toString()
	{
		return array(
			array('a', '=', 'b', ':left <=> :right'),
			array('a', '<>', 'b', 'NOT (:left <=> :right)'),
			array('a', '!=', 'b', 'NOT (:left <=> :right)'),
		);
	}

	/**
	 * @covers  SQL\MySQL\Identical::__toString
	 *
	 * @dataProvider    provider_toString
	 *
	 * @param   mixed   $left       First argument to the constructor
	 * @param   string  $operator   Second argument to the constructor
	 * @param   mixed   $right      Third argument to the constructor
	 * @param   string  $expected
	 */
	public function test_toString($left, $operator, $right, $expected)
	{
		$expression = new Identical($left, $operator, $right);

		$this->assertSame($expected, (string) $expression);
	}
}
