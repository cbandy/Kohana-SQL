<?php
namespace SQL\PDO;

/**
 * @package     SQL
 * @subpackage  PDO
 * @author      Chris Bandy
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('pdo'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('PDO extension not installed');
	}

	/**
	 * @coversNothing
	 */
	public function test_interfaces()
	{
		$class = new \ReflectionClass('SQL\PDO\Connection');

		$this->assertTrue($class->implementsInterface('SQL\Transactions'));
	}

	public function provider_constructor_generates_name()
	{
		return array(
			array(
				array(array('dsn' => '')),
				'kohana_cxn_da39a3ee5e6b4b0d3255bfef95601890afd80709',
			),
			array(
				array(array('dsn' => 'sqlite:file')),
				'kohana_cxn_140094d5d8e3a61fa42309131fa59a71f11321a5',
			),

			array(
				array(array('dsn' => ''), NULL),
				'kohana_cxn_da39a3ee5e6b4b0d3255bfef95601890afd80709',
			),
			array(
				array(array('dsn' => 'sqlite:file'), NULL),
				'kohana_cxn_140094d5d8e3a61fa42309131fa59a71f11321a5',
			),

			array(array(array(), 'some'), 'some'),
			array(array(array('dsn' => ''), 'string'), 'string'),
			array(array(array('dsn' => 'sqlite:file'), 'specified'), 'specified'),
		);
	}

	/**
	 * @covers  SQL\PDO\Connection::__construct
	 *
	 * @dataProvider    provider_constructor_generates_name
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $expected
	 */
	public function test_constructor_generates_name($arguments, $expected)
	{
		$class = new \ReflectionClass('SQL\PDO\Connection');
		$connection = $class->newInstanceArgs($arguments);

		$this->assertSame($expected, (string) $connection);
	}
}
