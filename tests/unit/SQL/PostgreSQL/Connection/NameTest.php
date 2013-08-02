<?php
namespace SQL\PostgreSQL;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @covers  SQL\PostgreSQL\Connection::__construct
 */
class Connection_NameTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor_generates_name_using_info()
	{
		return array(
			array(
				array('info' => ''),
				'kohana_cxn_pg_da39a3ee5e6b4b0d3255bfef95601890afd80709'
			),
			array(
				array('info' => 'dbname=db'),
				'kohana_cxn_pg_b8c1b280a19bb77c59fcd9d93cd32e227140ede5'
			),
			array(
				array('hostname' => 'internet', 'info' => 'dbname=db'),
				'kohana_cxn_pg_b8c1b280a19bb77c59fcd9d93cd32e227140ede5'
			),
		);
	}

	/**
	 * @dataProvider    provider_constructor_generates_name_using_info
	 *
	 * @param   array   $config
	 * @param   string  $expected
	 */
	public function test_constructor_generates_name_using_info($config, $expected)
	{
		$this->assertSame($expected, (string) new Connection($config));
	}

	public function provider_constructor_generates_name_without_info()
	{
		return array(
			array(
				array(),
				'kohana_cxn_pg_da39a3ee5e6b4b0d3255bfef95601890afd80709'
			),
			array(
				array('hostname' => 'internet'),
				'kohana_cxn_pg_74ae3099c808cb0b53230a687320f3fcf556801e'
			),
		);
	}

	/**
	 * @dataProvider    provider_constructor_generates_name_without_info
	 *
	 * @param   array   $config
	 * @param   string  $expected
	 */
	public function test_constructor_generates_name_without_info($config, $expected)
	{
		$this->assertSame($expected, (string) new Connection($config));
	}

	public function test_constructor_generates_name_when_null()
	{
		$this->assertSame(
			'kohana_cxn_pg_da39a3ee5e6b4b0d3255bfef95601890afd80709',
			(string) new Connection(array('info' => ''), NULL)
		);
	}

	public function test_constructor_generates_name_less_than_64_bytes()
	{
		$this->assertLessThan(64, strlen(new Connection(array())));
	}

	public function test_constructor_uses_supplied_name()
	{
		$this->assertSame(
			'some name', (string) new Connection(array(), 'some name')
		);
	}
}

