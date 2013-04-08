<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Compiler_QuoteLiteralTest extends \PHPUnit_Framework_TestCase
{
	public function provider_quote_array()
	{
		return array(
			array(array(), 'ARRAY[]'),
			array(array(NULL), 'ARRAY[NULL]'),
			array(array(FALSE), "ARRAY['0']"),
			array(array(TRUE), "ARRAY['1']"),

			array(array(51678), 'ARRAY[51678]'),
			array(array(12.345), 'ARRAY[1.234500E+1]'),

			array(array('string'), "ARRAY['string']"),
			array(array("multiline\nstring"), "ARRAY['multiline\nstring']"),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_array
	 *
	 * @dataProvider    provider_quote_array
	 *
	 * @param   array   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_array($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_array($value));
	}

	public function provider_quote_binary()
	{
		return array(
			array(NULL, "X''"),
			array('',   "X''"),

			array("\x0", "X'00'"),
			array("\200\0\350", "X'8000e8'"),
			array('ascii', "X'6173636969'"),
			array('0', "X'30'"),

			array(new Literal\Binary("\x0"), "X'00'"),
			array(new Literal\Binary('ascii'), "X'6173636969'"),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_binary
	 *
	 * @dataProvider    provider_quote_binary
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_binary($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_binary($value));
	}

	public function provider_quote_boolean()
	{
		return array(
			array(NULL,  "'0'"),
			array(FALSE, "'0'"),
			array(TRUE,  "'1'"),
			array(0,     "'0'"),
			array(-1,    "'1'"),
			array(51678, "'1'"),
			array(12.345, "'1'"),
			array('string', "'1'"),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_boolean
	 *
	 * @dataProvider    provider_quote_boolean
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_boolean($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_boolean($value));
	}

	public function provider_quote_datetime()
	{
		return array(
			array(
				new \DateTime('1990-05-27 14:23:57Z'),
				"'1990-05-27 14:23:57.000000+00:00'"
			),
			array(
				new \DateTime('1990-05-27 14:23:57.5-9'),
				"'1990-05-27 14:23:57.500000-09:00'"
			),
			array(
				new \DateTime('1990-05-27 14:23:57', new \DateTimeZone('UTC')),
				"'1990-05-27 14:23:57.000000+00:00'"
			),
			array(
				new \DateTime(
					'1990-05-27 14:23:57', new \DateTimeZone('Europe/Berlin')
				),
				"'1990-05-27 14:23:57.000000+02:00'"
			),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_datetime
	 *
	 * @dataProvider    provider_quote_datetime
	 *
	 * @param   DateTime    $value      Argument
	 * @param   string      $expected
	 */
	public function test_quote_datetime($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_datetime($value));
	}

	public function provider_quote_float()
	{
		return array(
			array(NULL,         '0.000000E+0'),
			array(FALSE,        '0.000000E+0'),
			array(TRUE,         '1.000000E+0'),
			array('123',        '1.230000E+2'),
			array('0.5',        '5.000000E-1'),

			array(0.00001,      '1.000000E-5'),
			array(1,            '1.000000E+0'),
			array(12.345,       '1.234500E+1'),
			array(1234567.89,   '1.234568E+6'),

			// Large
			array(1.2345E9,     '1.234500E+9'),
			array(1.2345E21,    '1.234500E+21'),

			// Small
			array(0.0000005,    '5.000000E-7'),
			array(1.2345E-9,    '1.234500E-9'),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_float
	 *
	 * @dataProvider    provider_quote_float
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_float($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_float($value));
	}

	public function provider_quote_integer()
	{
		return array(
			array(NULL,     '0'),
			array(FALSE,    '0'),
			array(TRUE,     '1'),
			array('90',     '90'),
			array(3.5,      '3'),

			array(1234, '1234'),
			array(51678, '51678'),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_integer
	 *
	 * @dataProvider    provider_quote_integer
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_integer($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_integer($value));
	}

	public function provider_quote_numeric()
	{
		return array(
			array(NULL,  9, '0.000000000'),
			array(FALSE, 0, '0'),
			array(TRUE,  1, '1.0'),
			array(0.777, 2, '0.78'),
			array(355/113, 3, '3.142'),

			array(12.345, NULL, '12.3450'),
			array(12.345, FALSE, '12'),
			array(12.345, TRUE, '12.3'),
			array(12.345, 2.7, '12.35'),
			array(12.345, 'a', '12'),

			array(new Literal\Numeric(12.345, 1), NULL, '12.3'),
			array(new Literal\Numeric(12.345, NULL), 1, '12.3'),
			array(new Literal\Numeric(12.345, NULL), NULL, '12.3450'),

			array(new Literal\Numeric(12.345, rand(5, 10)), 1, '12.3'),
			array(new Literal\Numeric(12.345, rand(7, 18)), 5, '12.34500'),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_numeric
	 *
	 * @dataProvider    provider_quote_numeric
	 *
	 * @param   mixed   $value      First argument
	 * @param   integer $scale      Second argument
	 * @param   string  $expected
	 */
	public function test_quote_numeric($value, $scale, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_numeric($value, $scale));
	}

	public function provider_quote_numeric_scale_defaults_to_four()
	{
		return array(
			array(NULL,     '0.0000'),
			array(FALSE,    '0.0000'),
			array(TRUE,     '1.0000'),
			array(55,       '55.0000'),
			array(0.77,     '0.7700'),
			array(355/113,  '3.1416'),

			array(new Literal\Numeric(12.345, NULL), '12.3450'),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_numeric
	 *
	 * @dataProvider    provider_quote_numeric_scale_defaults_to_four
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_numeric_scale_defaults_to_four($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_numeric($value));
	}

	public function provider_quote_string()
	{
		return array(
			array(NULL,     "''"),
			array(FALSE,    "''"),
			array(TRUE,     "'1'"),
			array(90,       "'90'"),
			array(1.2,      "'1.2'"),

			array('string', "'string'"),
			array("multiline\nstring", "'multiline\nstring'"),
			array("single'quote", "'single''quote'"),
			array("two''quotes", "'two''''quotes'"),
			array('double"quote', "'double\"quote'"),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_string
	 *
	 * @dataProvider    provider_quote_string
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_string($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_string($value));
	}

	/**
	 * @covers  SQL\Compiler::quote_string
	 */
	public function test_quote_string_object()
	{
		$compiler = new Compiler;

		$object = $this->getMock('stdClass', array('__toString'));
		$object
			->expects($this->once())
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$this->assertSame(
			"'object__toString'", $compiler->quote_string($object)
		);
	}

	public function provider_quote_literal()
	{
		return array(
			array(NULL, 'NULL'),
			array(FALSE, "'0'"),
			array(TRUE, "'1'"),

			array(0, '0'),
			array(-1, '-1'),
			array(51678, '51678'),
			array(12.345, '1.234500E+1'),

			array('string', "'string'"),
			array("multiline\nstring", "'multiline\nstring'"),
			array("single'quote", "'single''quote'"),
			array('double"quote', "'double\"quote'"),

			array(new Literal\Binary("\xDE\xAD\xBE\xEF"), "X'deadbeef'"),
			array(new Literal\Numeric(6.789, 2), '6.79'),
			array(
				new \DateTime('1990-05-27 14:23:57.8-9'),
				"'1990-05-27 14:23:57.800000-09:00'"
			),

			array(array(), 'ARRAY[]'),
			array(array(NULL), 'ARRAY[NULL]'),
			array(array(FALSE), "ARRAY['0']"),
			array(array(TRUE), "ARRAY['1']"),

			array(array(51678), 'ARRAY[51678]'),
			array(array(12.345), 'ARRAY[1.234500E+1]'),

			array(array('string'), "ARRAY['string']"),
			array(array("multiline\nstring"), "ARRAY['multiline\nstring']"),
			array(array("single'quote"), "ARRAY['single''quote']"),
			array(array('double"quote'), "ARRAY['double\"quote']"),

			array(
				array(new Literal\Binary("\xDE\xAD\xBE\xEF")),
				"ARRAY[X'deadbeef']"
			),
			array(array(new Literal\Numeric(6.789, 2)), 'ARRAY[6.79]'),
			array(
				array(new \DateTime('1990-05-27 14:23:57.8-9')),
				"ARRAY['1990-05-27 14:23:57.800000-09:00']"
			),

			array(new Literal(NULL), 'NULL'),
			array(new Literal(FALSE), "'0'"),
			array(new Literal(TRUE), "'1'"),

			array(new Literal(0), '0'),
			array(new Literal(-1), '-1'),
			array(new Literal(51678), '51678'),
			array(new Literal(12.345), '1.234500E+1'),

			array(new Literal('string'), "'string'"),
			array(new Literal("multiline\nstring"), "'multiline\nstring'"),
			array(new Literal("single'quote"), "'single''quote'"),
			array(new Literal('double"quote'), "'double\"quote'"),

			array(
				new Literal(new Literal\Binary("\xDE\xAD\xBE\xEF")),
				"X'deadbeef'"
			),
			array(new Literal(new Literal\Numeric(6.789, 2)), '6.79'),
			array(
				new Literal(new \DateTime('1990-05-27 14:23:57.8-9')),
				"'1990-05-27 14:23:57.800000-09:00'"
			),

			array(new Literal(array()), 'ARRAY[]'),
			array(new Literal(array(NULL)), 'ARRAY[NULL]'),
			array(new Literal(array(FALSE)), "ARRAY['0']"),
			array(new Literal(array(TRUE)), "ARRAY['1']"),

			array(new Literal(array(51678)), 'ARRAY[51678]'),
			array(new Literal(array(12.345)), 'ARRAY[1.234500E+1]'),

			array(new Literal(array('string')), "ARRAY['string']"),
			array(
				new Literal(array("multiline\nstring")),
				"ARRAY['multiline\nstring']"
			),
			array(new Literal(array("single'quote")), "ARRAY['single''quote']"),
			array(new Literal(array('double"quote')), "ARRAY['double\"quote']"),

			array(
				new Literal(array(new Literal\Binary("\xDE\xAD\xBE\xEF"))),
				"ARRAY[X'deadbeef']"
			),
			array(
				new Literal(array(new Literal\Numeric(6.789, 2))),
				'ARRAY[6.79]'
			),
			array(
				new Literal(array(new \DateTime('1990-05-27 14:23:57.8-9'))),
				"ARRAY['1990-05-27 14:23:57.800000-09:00']"
			),
		);
	}

	/**
	 * @covers  SQL\Compiler::is_a_literal
	 * @covers  SQL\Compiler::quote_literal
	 *
	 * @dataProvider    provider_quote_literal
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_literal($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_literal($value));
	}

	/**
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  SQL\Compiler::quote_literal
	 */
	public function test_quote_literal_object()
	{
		$compiler = new Compiler;

		$object = $this->getMock('stdClass', array('__toString'));
		$object
			->expects($this->exactly(2))
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$this->assertSame(
			"'object__toString'", $compiler->quote_literal($object)
		);
		$this->assertSame(
			"ARRAY['object__toString']",
			$compiler->quote_literal(array($object))
		);
	}
}
