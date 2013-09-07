<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @covers  SQL\MySQL\Connection::__construct
 */
class Connection_NameTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor_generates_name()
	{
		return array(
			array(
				array(),
				'kohana_cxn_mysql_a07dcdf9d02e90321d48dbac556c21222423eee8'
			),
			array(
				array('hostname' => 'a'),
				'kohana_cxn_mysql_5e537e393d36c23c1a806e255f7546d81c34ab0a'
			),
			array(
				array('database' => 'b'),
				'kohana_cxn_mysql_2ee436327c6fa5de7b67095f46c59810ca3ad0c6'
			),
		);
	}

	/**
	 * @dataProvider    provider_constructor_generates_name
	 *
	 * @param   array   $config
	 * @param   string  $expected
	 */
	public function test_constructor_genereates_name($config, $expected)
	{
		$this->assertSame($expected, (string) new Connection($config));
	}

	public function test_constructor_generates_name_when_null()
	{
		$this->assertSame(
			'kohana_cxn_mysql_2ee436327c6fa5de7b67095f46c59810ca3ad0c6',
			(string) new Connection(array('database' => 'b'), NULL)
		);
	}

	public function test_constructor_generates_name_less_than_64_characters()
	{
		$this->assertLessThan(64, mb_strlen(new Connection(array())));
	}

	public function test_constructor_uses_supplied_name()
	{
		$this->assertSame(
			'some name', (string) new Connection(array(), 'some name')
		);
	}
}
