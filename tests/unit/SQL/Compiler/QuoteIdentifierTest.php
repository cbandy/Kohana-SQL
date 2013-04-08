<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Compiler_QuoteIdentifierTest extends \PHPUnit_Framework_TestCase
{
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

		return array(
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

		return array(
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

		return array(
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
}
