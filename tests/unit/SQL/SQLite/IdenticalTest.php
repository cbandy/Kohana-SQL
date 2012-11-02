<?php
namespace SQL\SQLite;

/**
 * @package     SQL
 * @subpackage  SQLite
 * @author      Chris Bandy
 */
class IdenticalTest extends \PHPUnit_Framework_TestCase
{
	public function provider_toString()
	{
		return array(
			array('a', '=', 'b', ':left IS :right'),
			array('a', '<>', 'b', ':left IS NOT :right'),
			array('a', '!=', 'b', ':left IS NOT :right'),
		);
	}

	/**
	 * @covers  SQL\SQLite\Identical::__toString
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
		$identical = new Identical($left, $operator, $right);

		$this->assertSame($expected, (string) $identical);
	}
}
