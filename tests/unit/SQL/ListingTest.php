<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ListingTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(','), ',', array(), ''),
			array(array('-', array()), '-', array(), ''),
			array(array('::', array('a')), '::', array('a'), '?'),
			array(array(',', array('a', 'b')), ',', array('a', 'b'), '?,?'),
		);
	}

	/**
	 * @covers  SQL\Listing::__construct
	 * @covers  SQL\Listing::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $separator  Expected property
	 * @param   array   $values     Expected property
	 * @param   string  $value      Expected
	 */
	public function test_constructor($arguments, $separator, $values, $value)
	{
		$class = new \ReflectionClass('SQL\Listing');
		$list = $class->newInstanceArgs($arguments);

		$this->assertSame($separator, $list->separator);
		$this->assertSame($values, $list->values);

		$this->assertSame($value, (string) $list);
		$this->assertSame($values, $list->parameters);
	}
}
