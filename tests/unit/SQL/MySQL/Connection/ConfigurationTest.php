<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @covers  SQL\MySQL\Connection::configuration
 */
class Connection_ConfigurationTest extends \PHPUnit_Framework_TestCase
{
	protected $default = array(
		'hostname' => NULL,
		'port' => NULL,
		'socket' => NULL,
		'username' => NULL,
		'password' => NULL,
		'database' => NULL,
		'flags' => NULL,
		'options' => array(),
	);

	public function test_empty_array_produces_mostly_nulls()
	{
		$this->assertSame($this->default, Connection::configuration(array()));
	}

	protected function assert_identical_pass_through($key)
	{
		$value = new \stdClass;

		$this->assertSame(
			array_merge($this->default, array($key => $value)),
			Connection::configuration(array($key => $value))
		);
	}

	public function test_hostname_is_identical()
	{
		$this->assert_identical_pass_through('hostname');
	}

	public function test_port_is_identical()
	{
		$this->assert_identical_pass_through('port');
	}

	public function test_socket_is_identical()
	{
		$this->assert_identical_pass_through('socket');
	}

	public function test_username_is_identical()
	{
		$this->assert_identical_pass_through('username');
	}

	public function test_password_is_identical()
	{
		$this->assert_identical_pass_through('password');
	}

	public function test_flags_are_identical()
	{
		$this->assert_identical_pass_through('flags');
	}

	public function test_null_options_becomes_array()
	{
		$this->assertSame(
			$this->default, Connection::configuration(array('options' => NULL))
		);
	}

	public function test_persistent_prepends_to_hostname()
	{
		$this->assertSame(
			array_merge($this->default, array('hostname' => 'p:')),
			Connection::configuration(array('persistent' => TRUE))
		);

		$this->assertSame(
			array_merge($this->default, array('hostname' => 'p:a')),
			Connection::configuration(array('hostname' => 'a', 'persistent' => 'b'))
		);
	}

	public function test_complete_set()
	{
		$this->assertSame(
			array(
				'hostname' => 'p:j',
				'port' => 2,
				'socket' => 'k',
				'username' => 'l',
				'password' => 'm',
				'database' => 'n',
				'flags' => 3,
				'options' => array(4 => TRUE),
			),
			Connection::configuration(array(
				'hostname' => 'j', 'port' => 2, 'socket' => 'k',
				'username' => 'l', 'password' => 'm', 'database' => 'n',
				'flags' => 3, 'options' => array(4 => TRUE),
				'persistent' => TRUE,
			))
		);
	}
}
