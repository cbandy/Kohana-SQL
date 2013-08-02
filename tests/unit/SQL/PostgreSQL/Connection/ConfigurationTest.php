<?php
namespace SQL\PostgreSQL;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @covers  SQL\PostgreSQL\Connection::configuration
 */
class Connection_ConfigurationTest extends \PHPUnit_Framework_TestCase
{
	public function test_empty_array_produces_empty_string()
	{
		$this->assertSame('', Connection::configuration(array()));
	}

	public function test_hostname_becomes_host()
	{
		$this->assertSame(
			"host='a'", Connection::configuration(array('hostname' => 'a'))
		);
	}

	public function test_hostname_is_escaped()
	{
		$this->assertSame(
			"host='a\'b'", Connection::configuration(array('hostname' => "a'b"))
		);
	}

	public function test_port()
	{
		$this->assertSame(
			" port='1'", Connection::configuration(array('port' => 1))
		);
	}

	public function test_port_is_escaped()
	{
		$this->assertSame(
			" port='a\'b'", Connection::configuration(array('port' => "a'b"))
		);
	}

	public function test_username_becomes_user()
	{
		$this->assertSame(
			" user='b'", Connection::configuration(array('username' => 'b'))
		);
	}

	public function test_username_is_escaped()
	{
		$this->assertSame(
			" user='b\'c'", Connection::configuration(array('username' => "b'c"))
		);
	}

	public function test_password()
	{
		$this->assertSame(
			" password='c'", Connection::configuration(array('password' => 'c'))
		);
	}

	public function test_password_is_escaped()
	{
		$this->assertSame(
			" password='c\'d'", Connection::configuration(array('password' => "c'd"))
		);
	}

	public function test_database_becomes_dbname()
	{
		$this->assertSame(
			" dbname='d'", Connection::configuration(array('database' => 'd'))
		);
	}

	public function test_database_is_escaped()
	{
		$this->assertSame(
			" dbname='d\'e'", Connection::configuration(array('database' => "d'e"))
		);
	}

	public function test_options()
	{
		$this->assertSame(
			" options='--x=y'", Connection::configuration(array('options' => '--x=y'))
		);
	}

	public function test_options_are_escaped()
	{
		$this->assertSame(
			" options='--z=\'m n\''", Connection::configuration(array('options' => "--z='m n'"))
		);
	}

	public function test_ssl_false_becomes_sslmode_disable()
	{
		$this->assertSame(
			" sslmode='disable'", Connection::configuration(array('ssl' => FALSE))
		);
	}

	public function test_ssl_true_becomes_sslmode_require()
	{
		$this->assertSame(
			" sslmode='require'", Connection::configuration(array('ssl' => TRUE))
		);
	}

	public function test_ssl_becomes_sslmode()
	{
		$this->assertSame(
			" sslmode='s'", Connection::configuration(array('ssl' => 's'))
		);
	}

	public function test_ssl_is_escaped()
	{
		$this->assertSame(
			" sslmode='s\'t'", Connection::configuration(array('ssl' => "s't"))
		);
	}

	public function test_complete_connection_string()
	{
		$this->assertSame(
			"host='j' port='2' user='k' password='l' dbname='m' options='--n=o' sslmode='p'",
			Connection::configuration(array(
				'hostname' => 'j',
				'port' => 2,
				'username' => 'k',
				'password' => 'l',
				'database' => 'm',
				'options' => '--n=o',
				'ssl' => 'p',
			))
		);
	}
}
