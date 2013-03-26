<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), '', '"', '"'),

			array(array(''), '', '"', '"'),
			array(array('pre_'), 'pre_', '"', '"'),

			array(array('', '$'), '', '$', '$'),
			array(array('', array('a', 'b')), '', 'a', 'b'),

			array(array('pre_', '^'), 'pre_', '^', '^'),
			array(array('pre_', array('<', '>')), 'pre_', '<', '>'),
		);
	}

	/**
	 * @covers  SQL\Compiler::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $prefix     Expected table prefix
	 * @param   string  $left       Expected left identifier quote
	 * @param   string  $right      Expected right identifier quote
	 */
	public function test_constructor($arguments, $prefix, $left, $right)
	{
		$class = new \ReflectionClass('SQL\Compiler');
		$compiler = $class->newInstanceArgs($arguments);

		$this->assertSame($prefix, $compiler->table_prefix);
		$this->assertSame($left, $compiler->quote_left);
		$this->assertSame($right, $compiler->quote_right);
	}

	public function provider_parse_statement()
	{
		$result = array(
			array(new Expression(''), new Statement('')),

			// data set #1
			array(
				new Expression('?', array('a')),
				new Statement('?', array('a'))
			),
			array(
				new Expression('?', array(new Expression('a'))),
				new Statement('a')
			),
			array(
				new Expression('?', array(new Identifier('a'))),
				new Statement('"a"')
			),
			array(
				new Expression('?', array(new Table('a'))),
				new Statement('"pre_a"')
			),

			// data set #5
			array(
				new Expression(':a', array(':a' => 'b')),
				new Statement('?', array('b'))
			),
			array(
				new Expression(':a', array(':a' => new Expression('b'))),
				new Statement('b')
			),
			array(
				new Expression(':a', array(':a' => new Identifier('b'))),
				new Statement('"b"')
			),
			array(
				new Expression(':a', array(':a' => new Table('b'))),
				new Statement('"pre_b"')
			),

			// data set #9
			array(
				new Expression('?', array(array())),
				new Statement('')
			),
			array(
				new Expression('?', array(array('a', 'b'))),
				new Statement('?, ?', array('a', 'b'))
			),

			// data set #11
			array(
				new Expression('?', array(array(new Expression('a'), 'b'))),
				new Statement('a, ?', array('b'))
			),
			array(
				new Expression('?', array(array(new Identifier('a'), 'b'))),
				new Statement('"a", ?', array('b'))
			),
			array(
				new Expression('?', array(array(new Table('a'), 'b'))),
				new Statement('"pre_a", ?', array('b'))
			),

			// data set #14
			array(
				new Expression(':a', array(':a' => array('b', new Expression('c')))),
				new Statement('?, c', array('b'))
			),
			array(
				new Expression(':a', array(':a' => array('b', new Identifier('c')))),
				new Statement('?, "c"', array('b'))
			),
			array(
				new Expression(':a', array(':a' => array('b', new Table('c')))),
				new Statement('?, "pre_c"', array('b'))
			),

			// data set #17
			array(
				new Expression('?', array(new Literal('a'))),
				new Statement('?', array('a'))
			),
			array(
				new Expression('?', array(new Literal(array('a')))),
				new Statement('?', array(array('a'))),
			),
			array(
				new Expression('?', array(new Literal(new Expression('a')))),
				new Statement('?', array(new Expression('a'))),
			),
		);

		return $result;
	}

	/**
	 * @covers  SQL\Compiler::parse_statement
	 *
	 * @dataProvider    provider_parse_statement
	 *
	 * @param   Expression  $argument   Argument to the method
	 * @param   Statement   $expected   Expected result
	 */
	public function test_parse_statement($argument, $expected)
	{
		$compiler = new Compiler('pre_');

		$this->assertEquals($expected, $compiler->parse_statement($argument));
	}

	/**
	 * @covers  SQL\Compiler::parse_statement
	 */
	public function test_parse_statement_bound()
	{
		$compiler = new Compiler;

		$expression = new Expression('? :a');
		$expression->bind(0, $var);
		$expression->bind(':a', $var);

		$statement = $compiler->parse_statement($expression);

		$this->assertSame(
			array(0 => NULL, 1 => NULL), $statement->parameters()
		);

		$var = 1;
		$this->assertSame(
			array(0 => 1, 1 => 1), $statement->parameters()
		);
	}

	/**
	 * @covers  SQL\Compiler::parse_statement
	 */
	public function test_parse_statement_bound_literal()
	{
		$compiler = new Compiler;

		$literal = new Literal(1);
		$expression = new Expression('? :a');
		$expression->bind(0, $literal);
		$expression->bind(':a', $literal);

		$statement = $compiler->parse_statement($expression);

		$this->assertSame(
			array(0 => 1, 1 => 1), $statement->parameters()
		);

		$literal->value = 2;
		$this->assertSame(
			array(0 => 2, 1 => 2), $statement->parameters()
		);
	}

	public function provider_quote_array()
	{
		return array(
			array(array(), 'ARRAY[]'),
			array(array(NULL), 'ARRAY[NULL]'),
			array(array(FALSE), "ARRAY['0']"),
			array(array(TRUE), "ARRAY['1']"),

			array(array(51678), 'ARRAY[51678]'),
			array(array(12.345), 'ARRAY[12.345000]'),

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

	/**
	 * @covers  SQL\Compiler::quote_float
	 */
	public function test_quote_float()
	{
		$compiler = new Compiler;

		$this->assertSame('0.000010', $compiler->quote_float(0.00001));
		$this->assertSame('12.345000', $compiler->quote_float(12.345));
		$this->assertSame('1234567.890000', $compiler->quote_float(1234567.89));
	}

	/**
	 * @covers  SQL\Compiler::quote_float
	 */
	public function test_quote_large_float()
	{
		$compiler = new Compiler;

		$this->assertSame('1234500000.000000', $compiler->quote_float(1.2345E9));
		$this->assertSame(
			'1234499999999999868928.000000', $compiler->quote_float(1.2345E21)
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_float
	 */
	public function test_quote_tiny_float()
	{
		$compiler = new Compiler;

		$this->assertSame('0.000000', $compiler->quote_float(0.0000005));
		$this->assertSame('0.000000', $compiler->quote_float(1.2345E-9));
	}

	/**
	 * @covers  SQL\Compiler::quote_integer
	 */
	public function test_quote_integer()
	{
		$compiler = new Compiler;

		$this->assertSame('1234', $compiler->quote_integer(1234));
		$this->assertSame('51678', $compiler->quote_integer(51678));
	}

	/**
	 * @covers  SQL\Compiler::quote_string
	 */
	public function test_quote_string()
	{
		$compiler = new Compiler;

		$this->assertSame("'string'", $compiler->quote_string('string'));
		$this->assertSame(
			"'multiline\nstring'", $compiler->quote_string("multiline\nstring")
		);
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
		return array
		(
			array(NULL, 'NULL'),
			array(FALSE, "'0'"),
			array(TRUE, "'1'"),

			array(0, '0'),
			array(-1, '-1'),
			array(51678, '51678'),
			array(12.345, '12.345000'),

			array('string', "'string'"),
			array("multiline\nstring", "'multiline\nstring'"),

			array(array(), 'ARRAY[]'),
			array(array(NULL), 'ARRAY[NULL]'),
			array(array(FALSE), "ARRAY['0']"),
			array(array(TRUE), "ARRAY['1']"),

			array(array(51678), 'ARRAY[51678]'),
			array(array(12.345), 'ARRAY[12.345000]'),

			array(array('string'), "ARRAY['string']"),
			array(array("multiline\nstring"), "ARRAY['multiline\nstring']"),

			array(new Literal(NULL), 'NULL'),
			array(new Literal(FALSE), "'0'"),
			array(new Literal(TRUE), "'1'"),

			array(new Literal(0), '0'),
			array(new Literal(-1), '-1'),
			array(new Literal(51678), '51678'),
			array(new Literal(12.345), '12.345000'),

			array(new Literal('string'), "'string'"),
			array(new Literal("multiline\nstring"), "'multiline\nstring'"),

			array(new Literal(array()), 'ARRAY[]'),
			array(new Literal(array(NULL)), 'ARRAY[NULL]'),
			array(new Literal(array(FALSE)), "ARRAY['0']"),
			array(new Literal(array(TRUE)), "ARRAY['1']"),

			array(new Literal(array(51678)), 'ARRAY[51678]'),
			array(new Literal(array(12.345)), 'ARRAY[12.345000]'),

			array(new Literal(array('string')), "ARRAY['string']"),
			array(
				new Literal(array("multiline\nstring")),
				"ARRAY['multiline\nstring']"
			),
		);
	}

	/**
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

	public function provider_quote_identifier()
	{
		$one = new Identifier('one');

		$two_array = new Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new Identifier('two');
		$two_string->namespace = 'one';

		$three_array = new Identifier('three');
		$three_array->namespace = array('one','two');

		$three_identifier = new Identifier('three');
		$three_identifier->namespace = $two_identifier;

		$three_string = new Identifier('three');
		$three_string->namespace = 'one.two';

		return array
		(
			// Strings
			array('one',                '<one>'),
			array('one.two',            '<one>.<two>'),
			array('one.two.three',      '<one>.<two>.<three>'),
			array('one.two.three.four', '<one>.<two>.<three>.<four>'),

			// Arrays of strings
			array(array('one'),                      '<one>'),
			array(array('one','two'),                '<one>.<two>'),
			array(array('one','two','three'),        '<one>.<two>.<three>'),
			array(
				array('one','two','three','four'),
				'<one>.<two>.<three>.<four>'
			),

			// Identifier, no namespace
			array($one, '<one>'),

			// Identifier, one namespace
			array($two_array,      '<one>.<two>'),
			array($two_identifier, '<one>.<two>'),
			array($two_string,     '<one>.<two>'),

			// Identifier, two namespaces
			array($three_array,      '<one>.<two>.<three>'),
			array($three_identifier, '<one>.<two>.<three>'),
			array($three_string,     '<one>.<two>.<three>'),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_identifier
	 *
	 * @dataProvider    provider_quote_identifier
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_identifier($value, $expected)
	{
		$compiler = new Compiler('pre_', array('<', '>'));

		$this->assertSame($expected, $compiler->quote_identifier($value));
	}

	public function provider_quote_table()
	{
		$one = new Identifier('one');

		$two_array = new Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new Identifier('two');
		$two_string->namespace = 'one';

		return array
		(
			// Strings
			array('one',     '<pre_one>'),
			array('one.two', '<one>.<pre_two>'),

			// Array of strings
			array(array('one'),       '<pre_one>'),
			array(array('one','two'), '<one>.<pre_two>'),

			// Identifier, no namespace
			array($one, '<pre_one>'),

			// Identifier, one namespace
			array($two_array,      '<one>.<pre_two>'),
			array($two_identifier, '<one>.<pre_two>'),
			array($two_string,     '<one>.<pre_two>'),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_table
	 *
	 * @dataProvider    provider_quote_table
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_table($value, $expected)
	{
		$compiler = new Compiler('pre_', array('<', '>'));

		$this->assertSame($expected, $compiler->quote_table($value));
	}

	public function provider_quote_column()
	{
		$one = new Identifier('one');

		$two_array = new Identifier('two');
		$two_array->namespace = array('one');

		$two_identifier = new Identifier('two');
		$two_identifier->namespace = $one;

		$two_string = new Identifier('two');
		$two_string->namespace = 'one';

		$two_table = new Identifier('two');
		$two_table->namespace = new Table('one');

		$three_array = new Identifier('three');
		$three_array->namespace = array('one','two');

		$three_identifier = new Identifier('three');
		$three_identifier->namespace = $two_identifier;

		$three_string = new Identifier('three');
		$three_string->namespace = 'one.two';

		$three_table = new Identifier('three');
		$three_table->namespace = new Table('one.two');

		$one_star = new Identifier('*');
		$two_star = new Identifier('one.*');
		$three_star = new Identifier('one.two.*');

		return array
		(
			// Strings
			array('one',            '<one>'),
			array('one.two',        '<pre_one>.<two>'),
			array('one.two.three',  '<one>.<pre_two>.<three>'),

			// Array of strings
			array(array('one'),                 '<one>'),
			array(array('one','two'),           '<pre_one>.<two>'),
			array(array('one','two','three'),   '<one>.<pre_two>.<three>'),

			// Identifiers, no namespace
			array($one, '<one>'),

			// Identifiers, one namespace
			array($two_array,       '<pre_one>.<two>'),
			array($two_identifier,  '<one>.<two>'),
			array($two_string,      '<pre_one>.<two>'),
			array($two_table,       '<pre_one>.<two>'),

			// Identifiers, two namespaces
			array($three_array,         '<one>.<pre_two>.<three>'),
			array($three_identifier,    '<one>.<two>.<three>'),
			array($three_string,        '<one>.<pre_two>.<three>'),
			array($three_table,         '<one>.<pre_two>.<three>'),

			// Strings with asterisks
			array('*',          '*'),
			array('one.*',      '<pre_one>.*'),
			array('one.two.*',  '<one>.<pre_two>.*'),

			// Arrays of strings with asterisks
			array(array('*'),               '*'),
			array(array('one','*'),         '<pre_one>.*'),
			array(array('one','two','*'),   '<one>.<pre_two>.*'),

			// Identifiers with asterisks
			array($one_star,    '*'),
			array($two_star,    '<pre_one>.*'),
			array($three_star,  '<one>.<pre_two>.*'),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_column
	 *
	 * @dataProvider    provider_quote_column
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote_column($value, $expected)
	{
		$compiler = new Compiler('pre_', array('<', '>'));

		$this->assertSame($expected, $compiler->quote_column($value));
	}

	public function provider_quote_expression()
	{
		return array
		(
			// No arguments
			array(new Expression(''),       ''),
			array(new Expression('expr'),   'expr'),
			array(new Expression('?'),      '?'),
			array(new Expression(':param'), ':param'),

			// Empty
			array(new Expression('', array(NULL)), ''),
			array(new Expression('', array(1)),    ''),
			array(new Expression('', array('a')),  ''),

			// No parameters
			array(new Expression('expr', array(NULL)), 'expr'),
			array(new Expression('expr', array(1)),    'expr'),
			array(new Expression('expr', array('a')),  'expr'),

			// Positional parameter
			array(new Expression('?', array(NULL)), 'NULL'),
			array(new Expression('?', array(1)),    '1'),
			array(new Expression('?', array('a')),  "'a'"),

			array(
				new Expression('before ?', array(1)), 'before 1'
			),
			array(
				new Expression('? after', array(1)), '1 after'
			),
			array(
				new Expression('before ? after', array(1)), 'before 1 after'
			),

			// Positional Parameters
			array(
				new Expression('? split ?', array(1, 2)),
				'1 split 2'
			),
			array(
				new Expression('before ? split ?', array(1, 2)),
				'before 1 split 2'
			),
			array(
				new Expression('? split ? after', array(1, 2)),
				'1 split 2 after'
			),
			array(
				new Expression('before ? split ? after', array(1, 2)),
				'before 1 split 2 after'
			),

			// Named parameter
			array(new Expression(':param', array(':param' => NULL)), 'NULL'),
			array(new Expression(':param', array(':param' => 1)),    '1'),
			array(new Expression(':param', array(':param' => 'a')),  "'a'"),

			array(
				new Expression('before :param', array(':param' => 1)),
				'before 1'
			),
			array(
				new Expression(':param after', array(':param' => 1)),
				'1 after'
			),
			array(
				new Expression('before :param after', array(':param' => 1)),
				'before 1 after'
			),

			// Named parameters
			array(
				new Expression(
					':a split :b', array(':a' => 1, ':b' => 2)
				),
				'1 split 2'
			),
			array(
				new Expression(
					'before :a split :b', array(':a' => 1, ':b' => 2)
				),
				'before 1 split 2'
			),
			array(
				new Expression(
					':a split :b after', array(':a' => 1, ':b' => 2)
				),
				'1 split 2 after'
			),
			array(
				new Expression(
					'before :a split :b after', array(':a' => 1, ':b' => 2)
				),
				'before 1 split 2 after'
			),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_expression
	 *
	 * @dataProvider    provider_quote_expression
	 *
	 * @param   Expression  $value      Argument
	 * @param   string      $expected
	 */
	public function test_quote_expression($value, $expected)
	{
		$compiler = new Compiler;

		$this->assertSame($expected, $compiler->quote_expression($value));
	}

	public function provider_quote_expression_lacking_parameter()
	{
		return array
		(
			array(new Expression('?', array(1 => NULL))),
			array(new Expression('?', array(1 => 2))),
			array(new Expression('?', array(1 => 'a'))),

			array(new Expression(':param', array(NULL))),
			array(new Expression(':param', array(1))),
			array(new Expression(':param', array('a'))),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote_expression
	 *
	 * @dataProvider    provider_quote_expression_lacking_parameter
	 *
	 * @param   Expression  $value  Argument
	 */
	public function test_quote_expression_lacking_parameter($value)
	{
		$compiler = new Compiler;

		if (error_reporting() & E_NOTICE)
		{
			$exception = (class_exists('Kohana', FALSE) && Kohana::$errors)
				? 'ErrorException'
				: 'PHPUnit_Framework_Error_Notice';

			$this->setExpectedException($exception, 'Undefined', E_NOTICE);

			$compiler->quote_expression($value);
		}
		else
		{
			$this->assertSame('NULL', $compiler->quote_expression($value));
		}
	}

	public function provider_quote()
	{
		return array
		(
			// Literals
			array(NULL, 'NULL'),
			array(1,    '1'),
			array('a',  "'a'"),

			// Expression
			array(new Expression('expr'), 'expr'),

			// Identifiers
			array(new Identifier('one.two'),    '"one"."two"'),
			array(new Column('one.two'),        '"pre_one"."two"'),
			array(new Table('one.two'),         '"one"."pre_two"'),

			// Array
			array(array(NULL, 1, 'a'), "NULL, 1, 'a'"),

			// Literal Array
			array(new Literal(array(NULL, 1, 'a')), "ARRAY[NULL, 1, 'a']"),
		);
	}

	/**
	 * @covers  SQL\Compiler::quote
	 *
	 * @dataProvider    provider_quote
	 *
	 * @param   mixed   $value      Argument
	 * @param   string  $expected
	 */
	public function test_quote($value, $expected)
	{
		$compiler = new Compiler('pre_');

		$this->assertSame($expected, $compiler->quote($value));
	}

	/**
	 * Build the MockObject outside of a dataProvider.
	 *
	 * @covers  SQL\Compiler::quote
	 */
	public function test_quote_object()
	{
		$compiler = new Compiler;

		$object = $this->getMock('stdClass', array('__toString'));
		$object->expects($this->exactly(3))
			->method('__toString')
			->will($this->returnValue('object__toString'));

		$this->assertSame(
			"'object__toString'", $compiler->quote($object)
		);
		$this->assertSame(
			"'object__toString', 'object__toString'",
			$compiler->quote(array($object, $object))
		);
	}
}
