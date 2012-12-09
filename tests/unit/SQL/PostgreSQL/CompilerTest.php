<?php
namespace SQL\PostgreSQL;

use SQL\Expression;
use SQL\Identifier;
use SQL\Statement;
use SQL\Table;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{
	public function provider_parse_statement()
	{
		return array(
			array(new Expression(''), new Statement('')),

			// data set #1
			array(
				new Expression('?', array('a')),
				new Statement('$1', array('a'))
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
				new Statement('$1', array('b'))
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
				new Statement('$1, $2', array('a', 'b'))
			),

			// data set #11
			array(
				new Expression('?', array(array(new Expression('a'), 'b'))),
				new Statement('a, $1', array('b'))
			),
			array(
				new Expression('?', array(array(new Identifier('a'), 'b'))),
				new Statement('"a", $1', array('b'))
			),
			array(
				new Expression('?', array(array(new Table('a'), 'b'))),
				new Statement('"pre_a", $1', array('b'))
			),

			// data set #14
			array(
				new Expression(':a', array(':a' => array('b', new Expression('c')))),
				new Statement('$1, c', array('b'))
			),
			array(
				new Expression(':a', array(':a' => array('b', new Identifier('c')))),
				new Statement('$1, "c"', array('b'))
			),
			array(
				new Expression(':a', array(':a' => array('b', new Table('c')))),
				new Statement('$1, "pre_c"', array('b'))
			),

			// data set #17
			array(
				new Expression(':a :a', array(':a' => 'b')),
				new Statement('$1 $1', array('b'))
			),
			array(
				new Expression(':a :a', array(':a' => new Expression('b'))),
				new Statement('b b')
			),
			array(
				new Expression(':a :a', array(':a' => new Identifier('b'))),
				new Statement('"b" "b"')
			),
			array(
				new Expression(':a :a', array(':a' => new Table('b'))),
				new Statement('"pre_b" "pre_b"')
			),

			// data set #21
			array(
				new Expression(':a :a', array(':a' => new Expression('? ?', array('b', 'c')))),
				new Statement('$1 $2 $1 $2', array('b', 'c'))
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\Compiler::parse_statement
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
	 * @covers  SQL\PostgreSQL\Compiler::parse_statement
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

	public function provider_placeholder_with_type_cast()
	{
		return array(
			array(
				new Expression(':value::interval', array(':value' => '1 week')),
				new Statement('$1::interval', array('1 week')),
				"'1 week'::interval",
			),
			array(
				new Expression(
					"'yes':::type", array(':type' => new Expression('boolean'))
				),
				new Statement("'yes'::boolean"),
				"'yes'::boolean",
			),
		);
	}

	/**
	 * Double-colon can be used to type cast.
	 *
	 * @link http://www.postgresql.org/docs/current/static/sql-expressions.html#SQL-SYNTAX-TYPE-CASTS
	 *
	 * @covers  SQL\PostgreSQL\Compiler::parse_statement
	 * @covers  SQL\PostgreSQL\Compiler::quote_expression
	 *
	 * @dataProvider    provider_placeholder_with_type_cast
	 *
	 * @param   Expression  $expression
	 * @param   Statement   $statement
	 * @param   string      $quoted
	 */
	public function test_placeholder_with_type_cast($expression, $statement, $quoted)
	{
		$compiler = new Compiler;

		$this->assertEquals($statement, $compiler->parse_statement($expression));
		$this->assertSame($quoted, $compiler->quote($expression));
	}
}
