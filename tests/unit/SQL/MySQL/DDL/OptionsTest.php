<?php
namespace SQL\MySQL\DDL;

use SQL\Expression;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @covers  SQL\MySQL\DDL\Options::<protected>
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), array(), '', array()),
			array(
				array('ENGINE' => 'InnoDB'),
				array('ENGINE' => new Expression('InnoDB')),
				'ENGINE ?', array(new Expression('InnoDB')),
			),
			array(
				array('ENGINE' => 'InnoDB', 'OTHER' => 5),
				array('ENGINE' => new Expression('InnoDB'), 'OTHER' => 5),
				'ENGINE ? OTHER ?', array(new Expression('InnoDB'), 5),
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
	 * @param   array   $values     Expected property
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_constructor($argument, $values, $value, $parameters)
	{
		$options = new Options($argument);

		$this->assertEquals($values, $options->values);

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
	 * @param   array   $values     Expected property
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_values_assignment($argument, $values, $value, $parameters)
	{
		$options = new Options;
		$options->values = $argument;

		$this->assertEquals($values, $options->values);

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
		$options = new Options(array('AUTO_INCREMENT' => 5));

		$this->assertSame(5, $options['AUTO_INCREMENT']);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Options::offsetSet
	 */
	public function test_array_access_set()
	{
		$options = new Options(array('ENGINE' => 'InnoDB'));
		$options['AUTO_INCREMENT'] = 99;

		$this->assertSame(99, $options['AUTO_INCREMENT']);
		$this->assertEquals(
			array('ENGINE' => new Expression('InnoDB'), 'AUTO_INCREMENT' => 99),
			$options->values
		);

		$this->assertSame('ENGINE ? AUTO_INCREMENT ?', (string) $options);
		$this->assertEquals(
			array(new Expression('InnoDB'), 99), $options->parameters
		);
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
