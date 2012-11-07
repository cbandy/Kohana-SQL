<?php
namespace SQL\PostgreSQL;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class IdenticalTest extends \PHPUnit_Framework_TestCase
{
	public function provider_toString()
	{
		return array(
			array('a', '=', 'b', ':left IS NOT DISTINCT FROM :right'),
			array('a', '<>', 'b', ':left IS DISTINCT FROM :right'),
			array('a', '!=', 'b', ':left IS DISTINCT FROM :right'),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Identical::__toString
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
