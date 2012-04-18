<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class StatementTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(''),    '', array()),
			array(array('a'),   'a', array()),

			array(array('', array()),               '', array()),
			array(array('', array('b')),            '', array('b')),
			array(array('c', array('d')),           'c', array('d')),
			array(array('e', array(1 => 'f')),      'e', array(1 => 'f')),
			array(array('g', array('h' => 2)),      'g', array('h' => 2)),
			array(array('i', array('j' => 'k')),    'i', array('j' => 'k')),
		);
	}

	/**
	 * @covers  SQL\Statement
	 *
	 * @dataProvider  provider_constructor
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $value      Expected value
	 * @param   array   $parameters Expected parameters
	 */
	public function test_constructor($arguments, $value, $parameters)
	{
		$class = new \ReflectionClass('SQL\Statement');
		$statement = $class->newInstanceArgs($arguments);

		$this->assertSame($value, (string) $statement);
		$this->assertSame($parameters, $statement->parameters());
	}

	/**
	 * @covers  SQL\Statement::bind
	 */
	public function test_bind()
	{
		$statement = new Statement('');

		$this->assertSame(
			$statement, $statement->bind(0, $var), 'Chainable (integer)'
		);
		$this->assertSame(NULL, $var, 'Variable created');

		$this->assertSame(array(0 => NULL), $statement->parameters());

		$var = 1;
		$this->assertSame(array(0 => 1), $statement->parameters());

		$this->assertSame(
			$statement, $statement->bind(':a', $var), 'Chainable (string)'
		);
		$this->assertSame(1, $var, 'Variable unchanged');

		$this->assertSame(array(0 => 1, ':a' => 1), $statement->parameters());

		$var = 2;
		$this->assertSame(array(0 => 2, ':a' => 2), $statement->parameters());
	}

	/**
	 * @covers  SQL\Statement::param
	 */
	public function test_param()
	{
		$statement = new Statement('');

		$this->assertSame(
			$statement, $statement->param(0, NULL),
			'Chainable (integer, NULL)'
		);
		$this->assertSame(
			array(0 => NULL), $statement->parameters()
		);

		$this->assertSame(
			$statement, $statement->param(0, 1),
			'Chainable (integer, integer)'
		);
		$this->assertSame(
			array(0 => 1), $statement->parameters()
		);

		$this->assertSame(
			$statement, $statement->param(':a', NULL),
			'Chainable (string, NULL)'
		);
		$this->assertSame(
			array(0 => 1, ':a' => NULL), $statement->parameters()
		);

		$this->assertSame(
			$statement, $statement->param(':a', 2),
			'Chainable (string, integer)'
		);
		$this->assertSame(
			array(0 => 1, ':a' => 2), $statement->parameters()
		);
	}
}
