<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class IdenticalTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		$a = new \stdClass;
		$b = new \stdClass;

		return array(
			array(
				$a, '=', $b,
				'(NOT (:left <> :right OR :left IS NULL OR :right IS NULL)'
				.' OR (:left IS NULL AND :right IS NULL))',
				array(':left' => $a, ':right' => $b),
			),
			array(
				$a, '<>', $b,
				'((:left <> :right OR :left IS NULL OR :right IS NULL)'
				.' AND NOT (:left IS NULL AND :right IS NULL))',
				array(':left' => $a, ':right' => $b),
			),
			array(
				$a, '!=', $b,
				'((:left <> :right OR :left IS NULL OR :right IS NULL)'
				.' AND NOT (:left IS NULL AND :right IS NULL))',
				array(':left' => $a, ':right' => $b),
			),
		);
	}

	/**
	 * @covers  SQL\Identical::__construct
	 * @covers  SQL\Identical::__toString
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   mixed   $left       First argument to the constructor
	 * @param   string  $operator   Second argument to the constructor
	 * @param   mixed   $right      Third argument to the constructor
	 * @param   string  $value      Expected value
	 * @param   array   $parameters Expected parameters
	 */
	public function test_constructor($left, $operator, $right, $value, $parameters)
	{
		$identical = new Identical($left, $operator, $right);

		$this->assertSame($value, (string) $identical);
		$this->assertSame($parameters, $identical->parameters);
	}
}
