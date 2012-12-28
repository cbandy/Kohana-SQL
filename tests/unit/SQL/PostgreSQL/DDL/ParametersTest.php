<?php
namespace SQL\PostgreSQL\DDL;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class ParametersTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), '', array()),
			array(
				array('autovacuum_enabled' => TRUE),
				'autovacuum_enabled = ?', array(TRUE),
			),
			array(
				array('autovacuum_enabled' => TRUE, 'fillfactor' => 5),
				'autovacuum_enabled = ?, fillfactor = ?', array(TRUE, 5),
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Parameters::__construct
	 * @covers  SQL\PostgreSQL\DDL\Parameters::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_constructor($argument, $value, $parameters)
	{
		$params = new Parameters($argument);

		$this->assertSame($argument, $params->values);

		$this->assertSame($value, (string) $params);
		$this->assertEquals($parameters, $params->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Parameters::__construct
	 */
	public function test_constructor_default()
	{
		$params = new Parameters;

		$this->assertSame(array(), $params->values);

		$this->assertSame('', (string) $params);
		$this->assertSame(array(), $params->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Parameters::__get
	 * @covers  SQL\PostgreSQL\DDL\Parameters::__set
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_values_assignment($argument, $value, $parameters)
	{
		$params = new Parameters;
		$params->values = $argument;

		$this->assertSame($argument, $params->values);

		$this->assertSame($value, (string) $params);
		$this->assertEquals($parameters, $params->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Parameters::offsetExists
	 */
	public function test_array_access_exists()
	{
		$params = new Parameters(array('autovacuum_enabled' => TRUE));

		$this->assertTrue(isset($params['autovacuum_enabled']));
		$this->assertFalse(isset($params['fillfactor']));
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Parameters::offsetGet
	 */
	public function test_array_access_get()
	{
		$params = new Parameters(array('autovacuum_enabled' => TRUE));

		$this->assertSame(TRUE, $params['autovacuum_enabled']);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Parameters::offsetSet
	 */
	public function test_array_access_set()
	{
		$params = new Parameters(array('autovacuum_enabled' => TRUE));
		$params['fillfactor'] = 90;

		$this->assertSame(90, $params['fillfactor']);
		$this->assertSame(
			array('autovacuum_enabled' => TRUE, 'fillfactor' => 90),
			$params->values
		);

		$this->assertSame(
			'autovacuum_enabled = ?, fillfactor = ?', (string) $params
		);
		$this->assertSame(array(TRUE, 90), $params->parameters);
	}

	/**
	 * @covers  SQL\PostgreSQL\DDL\Parameters::offsetUnset
	 */
	public function test_array_access_unset()
	{
		$params = new Parameters(
			array('autovacuum_enabled' => TRUE, 'fillfactor' => 90)
		);
		unset($params['autovacuum_enabled']);

		$this->assertFalse(isset($params['autovacuum_enabled']));
		$this->assertSame(array('fillfactor' => 90), $params->values);

		$this->assertSame('fillfactor = ?', (string) $params);
		$this->assertSame(array(90), $params->parameters);
	}
}
