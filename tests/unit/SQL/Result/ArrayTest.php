<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Result_ArrayTest extends \PHPUnit_Framework_TestCase
{
	public function provider_constructor()
	{
		return array(
			array(array()),
			array(
				array(
					array('a' => 'A'),
					array('b' => 'B'),
				),
			),
		);
	}

	/**
	 * @covers  SQL\Result_Array::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $rows
	 */
	public function test_constructor($rows)
	{
		$result = new Result_Array($rows);

		$this->assertSame(count($rows), $result->count());
	}

	public function provider_current()
	{
		return array(
			array(
				array(
					array('a' => 'A'),
					array('b' => 'B'),
				),
				array('a' => 'A'),
			),
		);
	}

	/**
	 * @covers  SQL\Result_Array::current
	 *
	 * @dataProvider    provider_current
	 *
	 * @param   array   $rows       Data set
	 * @param   array   $expected
	 */
	public function test_current($rows, $expected)
	{
		$result = new Result_Array($rows);

		$this->assertSame($expected, $result->current());
		$this->assertSame($expected, $result->current(), 'Same position');
	}

	public function provider_current_after_seek()
	{
		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		$result[] = array(0, $rows, array('a' => 'A'));
		$result[] = array(1, $rows, array('b' => 'B'));
		$result[] = array(2, $rows, array('c' => 'C'));

		return $result;
	}

	/**
	 * @covers  SQL\Result_Array::current
	 *
	 * @dataProvider    provider_current_after_seek
	 *
	 * @param   integer $position
	 * @param   array   $rows       Data set
	 * @param   array   $expected
	 */
	public function test_current_after_seek($position, $rows, $expected)
	{
		$result = new Result_Array($rows);

		$result->seek($position);

		$this->assertSame($expected, $result->current());
		$this->assertSame($expected, $result->current(), 'Same position');
	}

	public function provider_offset_get()
	{
		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		$result[] = array($rows, 0, array('a' => 'A'));
		$result[] = array($rows, 1, array('b' => 'B'));
		$result[] = array($rows, 2, array('c' => 'C'));

		return $result;
	}

	/**
	 * @covers  SQL\Result_Array::offsetGet
	 *
	 * @dataProvider    provider_offset_get
	 *
	 * @param   array   $rows       Data set
	 * @param   integer $offset
	 * @param   array   $expected
	 */
	public function test_offset_get($rows, $offset, $expected)
	{
		$result = new Result_Array($rows);

		$this->assertSame($expected, $result->offsetGet($offset));
	}

	public function provider_offset_get_after_seek()
	{
		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		// data set #0
		$result[] = array($rows, 0, 0, array('a' => 'A'));
		$result[] = array($rows, 1, 0, array('a' => 'A'));
		$result[] = array($rows, 2, 0, array('a' => 'A'));

		// data set #3
		$result[] = array($rows, 0, 1, array('b' => 'B'));
		$result[] = array($rows, 1, 1, array('b' => 'B'));
		$result[] = array($rows, 2, 1, array('b' => 'B'));

		// data set #6
		$result[] = array($rows, 0, 2, array('c' => 'C'));
		$result[] = array($rows, 1, 2, array('c' => 'C'));
		$result[] = array($rows, 2, 2, array('c' => 'C'));

		return $result;
	}

	/**
	 * @covers  SQL\Result_Array::offsetGet
	 *
	 * @dataProvider    provider_offset_get_after_seek
	 *
	 * @param   array   $rows       Data set
	 * @param   integer $position
	 * @param   integer $offset
	 * @param   array   $expected
	 */
	public function test_offset_get_after_seek($rows, $position, $offset, $expected)
	{
		$result = new Result_Array($rows);
		$result->seek($position);

		$this->assertSame($expected, $result->offsetGet($offset));
		$this->assertSame($position, $result->key(), 'Same position');
	}

	public function provider_offset_get_invalid()
	{
		$rows = array(
			array('a' => 'A'),
			array('b' => 'B'),
			array('c' => 'C'),
		);

		$result[] = array($rows, -5);
		$result[] = array($rows, -1);
		$result[] = array($rows, 7);
		$result[] = array($rows, 8);
		$result[] = array($rows, 10);

		return $result;
	}

	/**
	 * @covers  SQL\Result_Array::offsetGet
	 *
	 * @dataProvider    provider_offset_get_invalid
	 *
	 * @param   array   $rows       Data set
	 * @param   integer $offset
	 */
	public function test_offset_get_invalid($rows, $offset)
	{
		$result = new Result_Array($rows);

		$this->assertNull($result->offsetGet($offset));
	}

	public function provider_to_array()
	{
		$rows = array(
			array('id' => 5, 'value' => 50),
			array('id' => 6, 'value' => 60),
			array('id' => 7, 'value' => 70),
		);

		// data set #0
		$result[] = array(NULL, NULL, $rows, $rows);

		// data set #1
		$result[] = array(NULL, 'id', $rows, array(5, 6, 7));
		$result[] = array(NULL, 'value', $rows, array(50, 60, 70));

		// data set #3
		$result[] = array(
			'id', 'value', $rows,
			array(5 => 50, 6 => 60, 7 => 70),
		);
		$result[] = array(
			'value', 'id', $rows,
			array(50 => 5, 60 => 6, 70 => 7),
		);

		// data set #5
		$result[] = array(
			'id', NULL, $rows,
			array(
				5 => array('id' => 5, 'value' => 50),
				6 => array('id' => 6, 'value' => 60),
				7 => array('id' => 7, 'value' => 70),
			),
		);
		$result[] = array(
			'value', NULL, $rows,
			array(
				50 => array('id' => 5, 'value' => 50),
				60 => array('id' => 6, 'value' => 60),
				70 => array('id' => 7, 'value' => 70),
			),
		);

		return $result;
	}

	/**
	 * @covers  SQL\Result_Array::to_array
	 *
	 * @dataProvider    provider_to_array
	 *
	 * @param   string  $key        First argument to method
	 * @param   string  $value      Second argument to method
	 * @param   array   $rows       Data set
	 * @param   array   $expected
	 */
	public function test_to_array($key, $value, $rows, $expected)
	{
		$result = new Result_Array($rows);

		$this->assertSame($expected, $result->to_array($key, $value));
	}
}
