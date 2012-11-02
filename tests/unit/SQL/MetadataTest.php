<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class MetadataTest extends \PHPUnit_Framework_TestCase
{
	public function provider_datatype()
	{
		return array(
			array(array('binary'), array('type' => 'binary', 'exact' => TRUE)),
			array(array('varchar'), array('type' => 'string')),

			array(array('blob', 'type'), 'binary'),
			array(array('float', 'type'), 'float'),
			array(array('integer', 'type'), 'integer'),
			array(array('varchar', 'type'), 'string'),

			array(array('not-a-type'), array()),
			array(array('not-a-type', 'type'), NULL),
		);
	}

	/**
	 * @covers  SQL\Metadata::datatype
	 *
	 * @dataProvider    provider_datatype
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   mixed   $expected
	 */
	public function test_datatype($arguments, $expected)
	{
		$this->assertSame(
			$expected,
			call_user_func_array(array(new Metadata, 'datatype'), $arguments)
		);
	}
}
