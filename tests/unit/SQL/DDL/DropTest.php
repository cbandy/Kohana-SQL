<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class DropTest extends \PHPUnit_Framework_TestCase
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
			array(array('a'), 'a', NULL, NULL, 'DROP A :names'),
			array(
				array('a', 'b'),
				'a', array(new Identifier('b')), NULL,
				'DROP A :names',
			),

			array(
				array('a', 'b', FALSE),
				'a', array(new Identifier('b')), FALSE,
				'DROP A :names RESTRICT',
			),
			array(
				array('a', 'b', TRUE),
				'a', array(new Identifier('b')), TRUE,
				'DROP A :names CASCADE',
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Drop::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $type       Expected property
	 * @param   array   $names      Expected property
	 * @param   boolean $cascade    Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $type, $names, $cascade, $value)
	{
		$class = new \ReflectionClass('SQL\DDL\Drop');
		$drop = $class->newInstanceArgs($arguments);

		$this->assertSame($type, $drop->type);
		$this->assertEquals($names, $drop->names);
		$this->assertSame($cascade, $drop->cascade);

		$this->assertSame($value, (string) $drop);
		$this->assertEquals(
			array_merge($this->parameters, array(':names' => $names)),
			$drop->parameters
		);
	}

	public function provider_cascade()
	{
		return array(
			array(array(), TRUE, 'DROP A :names CASCADE'),
			array(array(NULL), NULL, 'DROP A :names'),
			array(array(FALSE), FALSE, 'DROP A :names RESTRICT'),
			array(array(TRUE), TRUE, 'DROP A :names CASCADE'),
		);
	}

	/**
	 * @covers  SQL\DDL\Drop::cascade
	 *
	 * @dataProvider    provider_cascade
	 *
	 * @param   array   $arguments  Arguments
	 * @param   boolean $cascade    Expected
	 * @param   string  $value
	 */
	public function test_cascade($arguments, $cascade, $value)
	{
		$drop = new Drop('a');

		$this->assertSame(
			$drop, call_user_func_array(array($drop, 'cascade'), $arguments)
		);
		$this->assertSame($cascade, $drop->cascade);

		$this->assertSame($value, (string) $drop);
		$this->assertSame($this->parameters, $drop->parameters);
	}

	public function provider_if_exists()
	{
		return array(
			array(array(), TRUE, 'DROP B IF EXISTS :names'),
			array(array(NULL), NULL, 'DROP B :names'),
			array(array(FALSE), FALSE, 'DROP B :names'),
			array(array(TRUE), TRUE, 'DROP B IF EXISTS :names'),
		);
	}

	/**
	 * @covers  SQL\DDL\Drop::if_exists
	 *
	 * @dataProvider    provider_if_exists
	 *
	 * @param   array   $arguments  Arguments
	 * @param   boolean $if_exists  Expected
	 * @param   string  $value
	 */
	public function test_if_exists($arguments, $if_exists, $value)
	{
		$drop = new Drop('b');

		$this->assertSame(
			$drop, call_user_func_array(array($drop, 'if_exists'), $arguments)
		);
		$this->assertSame($if_exists, $drop->if_exists);

		$this->assertSame($value, (string) $drop);
		$this->assertSame($this->parameters, $drop->parameters);
	}

	public function provider_name()
	{
		return array(
			array('a', array(new Identifier('a'))),
			array(new Identifier('b'), array(new Identifier('b'))),
			array(new Expression('expr'), array(new Expression('expr'))),
		);
	}

	/**
	 * @covers  SQL\DDL\Drop::name
	 *
	 * @dataProvider    provider_name
	 *
	 * @param   mixed   $argument   Argument
	 * @param   array   $names      Expected
	 */
	public function test_name($argument, $names)
	{
		$drop = new Drop('x');

		$this->assertSame($drop, $drop->name($argument));
		$this->assertEquals($names, $drop->names);

		$this->assertSame('DROP X :names', (string) $drop);
		$this->assertEquals(
			array_merge($this->parameters, array(':names' => $names)),
			$drop->parameters
		);
	}

	public function provider_names()
	{
		return array(
			array(NULL, NULL),

			array(array('a'), array(new Identifier('a'))),
			array(
				array('a', 'b'),
				array(new Identifier('a'), new Identifier('b')),
			),

			array(array(new Identifier('a')), array(new Identifier('a'))),
			array(
				array(new Identifier('a'), new Identifier('b')),
				array(new Identifier('a'), new Identifier('b')),
			),

			array(array(new Expression('a')), array(new Expression('a'))),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DDL\Drop::names
	 *
	 * @dataProvider    provider_names
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $names      Expected
	 */
	public function test_names($argument, $names)
	{
		$drop = new Drop('y');

		$this->assertSame($drop, $drop->names($argument));
		$this->assertEquals($names, $drop->names);

		$this->assertSame('DROP Y :names', (string) $drop);
		$this->assertEquals(
			array_merge($this->parameters, array(':names' => $names)),
			$drop->parameters
		);
	}

	/**
	 * @covers  SQL\DDL\Drop::names
	 *
	 * @dataProvider    provider_names
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_names_reset($argument)
	{
		$drop = new Drop('x');
		$drop->names($argument);

		$this->assertSame($drop, $drop->names(NULL));
		$this->assertNull($drop->names);

		$this->assertSame('DROP X :names', (string) $drop);
		$this->assertSame($this->parameters, $drop->parameters);
	}

	/**
	 * @covers  SQL\DDL\Drop::__toString
	 */
	public function test_toString()
	{
		$drop = new Drop('z');
		$drop
			->if_exists()
			->cascade();

		$this->assertSame('DROP Z IF EXISTS :names CASCADE', (string) $drop);

		$drop->cascade(FALSE);
		$this->assertSame('DROP Z IF EXISTS :names RESTRICT', (string) $drop);
	}
}
