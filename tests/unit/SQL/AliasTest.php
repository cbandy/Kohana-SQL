<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class AliasTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(NULL, NULL, array(NULL, new Identifier(NULL))),

			array(0, 'a', array(0, new Identifier('a'))),
			array(1, array('a'), array(1, new Identifier('a'))),

			array(5, new Expression('a'), array(5, new Expression('a'))),
			array(6, new Identifier('a'), array(6, new Identifier('a'))),
		);
	}

	/**
	 * @covers  SQL\Alias::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   mixed                               $value
	 * @param   array|string|Expression|Identifier  $name
	 * @param   array                               $parameters
	 */
	public function test_constructor($value, $name, $parameters)
	{
		$alias = new Alias($value, $name);

		$this->assertSame('? AS ?', (string) $alias);
		$this->assertEquals($parameters, $alias->parameters);
	}
}
