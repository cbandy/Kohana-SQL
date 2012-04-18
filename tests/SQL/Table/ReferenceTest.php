<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Table_ReferenceTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array(), '', array()),
			array(array('one'), '?', array(new Table('one'))),
			array(
				array('one', 'a'),
				'? AS ?', array(new Table('one'), new Identifier('a')),
			),
		);
	}

	/**
	 * @covers  SQL\Table_Reference::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $value      Expected value
	 * @param   array   $parameters Expected parameters
	 */
	public function test_constructor($arguments, $value, $parameters)
	{
		$class = new \ReflectionClass('SQL\Table_Reference');
		$reference = $class->newInstanceArgs($arguments);

		$this->assertSame($value, (string) $reference);
		$this->assertEquals($parameters, $reference->parameters);
	}

	/**
	 * @covers  SQL\Table_Reference::add
	 * @covers  SQL\Table_Reference::add_reference
	 */
	public function test_add()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->add('two', 'b'));
		$this->assertSame('?, ? AS ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two'), new Identifier('b')),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::join
	 */
	public function test_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->join('two', 'b'));
		$this->assertSame('? JOIN ? AS ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table ('two'), new Identifier('b')),
			$reference->parameters
		);

		$this->assertSame($reference, $reference->join('three', NULL, 'left'));
		$this->assertSame('? JOIN ? AS ? LEFT JOIN ?', (string) $reference);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'), new Identifier('b'),
				new Table('three'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::cross_join
	 */
	public function test_cross_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->cross_join('two'));
		$this->assertSame('? CROSS JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame($reference, $reference->cross_join('three', 'a'));
		$this->assertSame(
			'? CROSS JOIN ? CROSS JOIN ? AS ?', (string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::full_join
	 */
	public function test_full_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->full_join('two'));
		$this->assertSame('? FULL JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame($reference, $reference->full_join('three', 'a'));
		$this->assertSame(
			'? FULL JOIN ? FULL JOIN ? AS ?', (string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::inner_join
	 */
	public function test_inner_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->inner_join('two'));
		$this->assertSame('? INNER JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame($reference, $reference->inner_join('three', 'a'));
		$this->assertSame(
			'? INNER JOIN ? INNER JOIN ? AS ?', (string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::left_join
	 */
	public function test_left_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->left_join('two'));
		$this->assertSame('? LEFT JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame($reference, $reference->left_join('three', 'a'));
		$this->assertSame(
			'? LEFT JOIN ? LEFT JOIN ? AS ?', (string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::right_join
	 */
	public function test_right_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->right_join('two'));
		$this->assertSame('? RIGHT JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame($reference, $reference->right_join('three', 'a'));
		$this->assertSame(
			'? RIGHT JOIN ? RIGHT JOIN ? AS ?', (string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::natural_full_join
	 */
	public function test_natural_full_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->natural_full_join('two'));
		$this->assertSame('? NATURAL FULL JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame(
			$reference, $reference->natural_full_join('three', 'a')
		);
		$this->assertSame(
			'? NATURAL FULL JOIN ? NATURAL FULL JOIN ? AS ?',
			(string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::natural_join
	 */
	public function test_natural_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->natural_join('two'));
		$this->assertSame('? NATURAL JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame(
			$reference, $reference->natural_join('three', 'a')
		);
		$this->assertSame(
			'? NATURAL JOIN ? NATURAL JOIN ? AS ?', (string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::natural_left_join
	 */
	public function test_natural_left_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->natural_left_join('two'));
		$this->assertSame('? NATURAL LEFT JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame(
			$reference, $reference->natural_left_join('three', 'a')
		);
		$this->assertSame(
			'? NATURAL LEFT JOIN ? NATURAL LEFT JOIN ? AS ?',
			(string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::natural_right_join
	 */
	public function test_natural_right_join()
	{
		$reference = new Table_Reference('one');

		$this->assertSame($reference, $reference->natural_right_join('two'));
		$this->assertSame('? NATURAL RIGHT JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$this->assertSame(
			$reference, $reference->natural_right_join('three', 'a')
		);
		$this->assertSame(
			'? NATURAL RIGHT JOIN ? NATURAL RIGHT JOIN ? AS ?',
			(string) $reference
		);
		$this->assertEquals(
			array(
				new Table('one'),
				new Table('two'),
				new Table('three'), new Identifier('a'),
			),
			$reference->parameters
		);
	}

	public function provider_on()
	{
		return array(
			array(
				array(
					new Conditions(
						new Column('one.x'), '=', new Column('two.x')
					),
				),
				'? JOIN ? ON (?)',
				array(
					new Table('one'), new Table('two'),
					new Conditions(
						new Column('one.x'), '=', new Column('two.x')
					),
				),
			),
			array(
				array(new Column('one.x'), '=', new Column('two.x')),
				'? JOIN ? ON (?)',
				array(
					new Table('one'), new Table('two'),
					new Conditions(
						new Column('one.x'), '=', new Column('two.x')
					),
				),
			),
			array(
				array('one.x', '=', 'two.x'),
				'? JOIN ? ON (?)',
				array(
					new Table('one'), new Table('two'),
					new Conditions(
						new Column('one.x'), '=', new Column('two.x')
					),
				),
			),
		);
	}

	/**
	 * @covers  SQL\Table_Reference::on
	 *
	 * @dataProvider    provider_on
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   array   $value      Expected value
	 * @param   array   $parameters Expected parameters
	 */
	public function test_on($arguments, $value, $parameters)
	{
		$reference = new Table_Reference('one');
		$reference->join('two');

		$this->assertSame(
			$reference,
			call_user_func_array(array($reference, 'on'), $arguments)
		);
		$this->assertSame($value, (string) $reference);
		$this->assertEquals($parameters, $reference->parameters);
	}

	/**
	 * @covers  SQL\Table_Reference::open
	 * @covers  SQL\Table_Reference::close
	 */
	public function test_parentheses()
	{
		$reference = new Table_Reference;

		$this->assertSame($reference, $reference->open());
		$this->assertSame('(', (string) $reference);
		$this->assertSame(array(), $reference->parameters);

		$reference->add('one');
		$this->assertSame('(?', (string) $reference);
		$this->assertEquals(array(new Table('one')), $reference->parameters);

		$this->assertSame($reference, $reference->open());
		$this->assertSame('(?, (', (string) $reference);
		$this->assertEquals(array(new Table('one')), $reference->parameters);

		$reference->add('two');
		$this->assertSame('(?, (?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two')), $reference->parameters
		);

		$reference->join('three');
		$this->assertSame('(?, (? JOIN ?', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two'), new Table('three')),
			$reference->parameters
		);

		$this->assertSame($reference, $reference->close());
		$this->assertSame('(?, (? JOIN ?)', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two'), new Table('three')),
			$reference->parameters
		);

		$this->assertSame($reference, $reference->close());
		$this->assertSame('(?, (? JOIN ?))', (string) $reference);
		$this->assertEquals(
			array(new Table('one'), new Table('two'), new Table('three')),
			$reference->parameters
		);
	}

	/**
	 * @covers  SQL\Table_Reference::using
	 */
	public function test_using()
	{
		$reference = new Table_Reference('one');
		$reference->join('two');

		$this->assertSame($reference, $reference->using(array('x', 'y')));
		$this->assertSame('? JOIN ? USING (?)', (string) $reference);
		$this->assertEquals(
			array(
				new Table('one'), new Table('two'),
				array(new Column('x'), new Column('y')),
			),
			$reference->parameters
		);
	}
}
