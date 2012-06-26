<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Drop_TableTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':names' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL, 'DROP TABLE :names'),
			array(array('a'), array(new Table('a')), NULL, 'DROP TABLE :names'),

			array(
				array('a', FALSE), array(new Table('a')), FALSE,
				'DROP TABLE :names RESTRICT',
			),
			array(
				array('a', TRUE), array(new Table('a')), TRUE,
				'DROP TABLE :names CASCADE',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Drop_Table::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $names      Expected property
	 * @param   boolean $cascade    Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $names, $cascade, $value)
	{
		$class = new \ReflectionClass('SQL\DDL\Drop_Table');
		$drop = $class->newInstanceArgs($arguments);

		$this->assertSame('TABLE', $drop->type);
		$this->assertEquals($names, $drop->names);
		$this->assertSame($cascade, $drop->cascade);

		$this->assertSame($value, (string) $drop);
		$this->assertEquals(
			array_merge($this->parameters, array(':names' => $names)),
			$drop->parameters
		);
	}

	public function provider_name()
	{
		return array(
			array('a', array(new Table('a'))),
			array(new Identifier('b'), array(new Identifier('b'))),
			array(new Expression('expr'), array(new Expression('expr'))),
		);
	}

	/**
	 * @covers  SQL\DDL\Drop_Table::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $argument   Argument
	 * @param   array   $names      Expected
	 */
	public function test_name($argument, $names)
	{
		$drop = new Drop_Table;

		$this->assertSame($drop, $drop->name($argument));
		$this->assertEquals($names, $drop->names);

		$this->assertSame('DROP TABLE :names', (string) $drop);
		$this->assertEquals(
			array_merge($this->parameters, array(':names' => $names)),
			$drop->parameters
		);
	}
}
