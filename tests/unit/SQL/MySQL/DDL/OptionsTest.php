<?php
namespace SQL\MySQL\DDL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), '', array()),
			array(array('ENGINE' => 'InnoDB'), 'ENGINE ?', array('InnoDB')),
			array(
				array('ENGINE' => 'InnoDB', 'OTHER' => 5),
				'ENGINE ? OTHER ?',
				array('InnoDB', 5),
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::__construct
	 * @covers  SQL\MySQL\DDL\Options::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_constructor($argument, $value, $parameters)
	{
		$options = new Options($argument);

		$this->assertSame($argument, $options->values);

		$this->assertSame($value, (string) $options);
		$this->assertEquals($parameters, $options->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::__construct
	 */
	public function test_constructor_default()
	{
		$options = new Options;

		$this->assertSame(array(), $options->values);

		$this->assertSame('', (string) $options);
		$this->assertSame(array(), $options->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::__get
	 * @covers  SQL\MySQL\DDL\Options::__set
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_values_assignment($argument, $value, $parameters)
	{
		$options = new Options;
		$options->values = $argument;

		$this->assertSame($argument, $options->values);

		$this->assertSame($value, (string) $options);
		$this->assertEquals($parameters, $options->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::offsetExists
	 */
	public function test_array_access_exists()
	{
		$options = new Options(array('ENGINE' => 'InnoDB'));

		$this->assertTrue(isset($options['ENGINE']));
		$this->assertFalse(isset($options['COMMENT']));
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::offsetGet
	 */
	public function test_array_access_get()
	{
		$options = new Options(array('ENGINE' => 'InnoDB'));

		$this->assertSame('InnoDB', $options['ENGINE']);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::offsetSet
	 */
	public function test_array_access_set()
	{
		$options = new Options(array('ENGINE' => 'InnoDB'));
		$options['AUTO_INCREMENT'] = 99;

		$this->assertSame(99, $options['AUTO_INCREMENT']);
		$this->assertSame(
			array('ENGINE' => 'InnoDB', 'AUTO_INCREMENT' => 99),
			$options->values
		);

		$this->assertSame('ENGINE ? AUTO_INCREMENT ?', (string) $options);
		$this->assertSame(array('InnoDB', 99), $options->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::offsetUnset
	 */
	public function test_array_access_unset()
	{
		$options = new Options(
			array('ENGINE' => 'InnoDB', 'AUTO_INCREMENT' => 99)
		);
		unset($options['ENGINE']);

		$this->assertFalse(isset($options['ENGINE']));
		$this->assertSame(array('AUTO_INCREMENT' => 99), $options->values);

		$this->assertSame('AUTO_INCREMENT ?', (string) $options);
		$this->assertSame(array(99), $options->parameters);
	}
}
