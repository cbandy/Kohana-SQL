<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ValuesTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		$result[] = array(array(), array());
		$result[] = array(array(array('a')), array(array('a')));

		return $result;
	}

	/**
	 * @covers  SQL\Values::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments to the constructor
	 * @param   string  $rows       Expected rows
	 */
	public function test_constructor($arguments, $rows)
	{
		$class = new \ReflectionClass('SQL\Values');
		$values = $class->newInstanceArgs($arguments);

		$this->assertEquals($rows, $values->rows);

		$this->assertSame('VALUES (?)', (string) $values);
		$this->assertEquals($rows, $values->parameters);
	}

	public function provider_row()
	{
		$result[] = array(NULL, array());
		$result[] = array(array(), array(array()));
		$result[] = array(array('a'), array(array('a')));
		$result[] = array(array(3, 4, 5), array(array(3, 4, 5)));

		return $result;
	}

	/**
	 * @covers  SQL\Values::row
	 *
	 * @dataProvider    provider_row
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $rows       Expected rows
	 */
	public function test_row($argument, $rows)
	{
		$values = new Values;

		$this->assertSame($values, $values->row($argument));
		$this->assertEquals($rows, $values->rows);

		$this->assertSame('VALUES (?)', (string) $values);
		$this->assertEquals($rows, $values->parameters);
	}

	/**
	 * @covers  SQL\Values::row
	 *
	 * @dataProvider    provider_row
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_row_reset($argument)
	{
		$values = new Values;
		$values->row($argument);

		$this->assertSame($values, $values->row(NULL));
		$this->assertSame(array(), $values->rows);

		$this->assertSame('VALUES (?)', (string) $values);
		$this->assertSame(array(), $values->parameters);
	}

	public function provider_rows()
	{
		$result[] = array(NULL, array(), 'VALUES (?)');
		$result[] = array(array(), array(), 'VALUES (?)');

		$result[] = array(array(array('a')), array(array('a')), 'VALUES (?)');

		$result[] = array(
			array(array('a'), array('b')),
			array(array('a'), array('b')),
			'VALUES (?), (?)',
		);

		$result[] = array(
			array(array(3, 4, 5), array(6, 7, 8)),
			array(array(3, 4, 5), array(6, 7, 8)),
			'VALUES (?), (?)',
		);

		return $result;
	}

	/**
	 * @covers  SQL\Values::rows
	 *
	 * @dataProvider    provider_rows
	 *
	 * @param   array   $argument   Argument
	 * @param   string  $rows       Expected rows
	 * @param   string  $value      Expected value
	 */
	public function test_rows($argument, $rows, $value)
	{
		$values = new Values;

		$this->assertSame($values, $values->rows($argument));
		$this->assertEquals($rows, $values->rows);

		$this->assertSame($value, (string) $values);
		$this->assertEquals($rows, $values->parameters);
	}

	/**
	 * @covers  SQL\Values::__toString
	 */
	public function test_toString()
	{
		$values = new Values;

		$values->row(array('a'));
		$this->assertSame('VALUES (?)', (string) $values);

		$values->row(array('b'));
		$this->assertSame('VALUES (?), (?)', (string) $values);

		$values->row(array('c'));
		$this->assertSame('VALUES (?), (?), (?)', (string) $values);
	}
}
