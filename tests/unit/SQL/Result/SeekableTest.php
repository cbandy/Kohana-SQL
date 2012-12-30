<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Result_SeekableTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @coversNothing
	 */
	public function test_interfaces()
	{
		$class = new \ReflectionClass('SQL\Result_Seekable');

		$this->assertTrue($class->implementsInterface('ArrayAccess'));
		$this->assertTrue($class->implementsInterface('Countable'));
		$this->assertTrue($class->implementsInterface('Iterator'));
		$this->assertTrue($class->implementsInterface('SeekableIterator'));
		$this->assertTrue($class->implementsInterface('Traversable'));
	}

	public function provider_count()
	{
		return array(
			array(0),
			array(1),
			array(2),
		);
	}

	/**
	 * @covers  SQL\Result_Seekable::__construct
	 * @covers  SQL\Result_Seekable::count
	 *
	 * @dataProvider    provider_count
	 *
	 * @param   integer $count
	 */
	public function test_count($count)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		$this->assertSame($count, $result->count());
	}

	public function provider_seek()
	{
		return array(
			array(1, 0),
			array(2, 0),
			array(2, 1),
			array(3, 0),
			array(3, 1),
			array(3, 2),
		);
	}

	/**
	 * @covers  SQL\Result_Seekable::seek
	 *
	 * @dataProvider    provider_seek
	 *
	 * @param   integer $count
	 * @param   integer $position
	 */
	public function test_seek($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		$this->assertSame($result, $result->seek($position));
		$this->assertSame($position, $result->key());
	}

	/**
	 * @covers  SQL\Result_Seekable::seek
	 *
	 * @dataProvider    provider_count
	 *
	 * @param   integer $count
	 */
	public function test_seek_error_low($count)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		$this->setExpectedException('OutOfBoundsException');

		$result->seek(-1);
	}

	public function provider_seek_error_high()
	{
		return array(
			array(1, 1),
			array(1, 5),
			array(2, 2),
			array(2, 3),
			array(3, 3),
			array(3, 7),
		);
	}

	/**
	 * @covers  SQL\Result_Seekable::seek
	 *
	 * @dataProvider    provider_seek_error_high
	 *
	 * @param   integer $count
	 * @param   integer $position
	 */
	public function test_seek_error_high($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		$this->setExpectedException('OutOfBoundsException');

		$result->seek($position);
	}

	/**
	 * @covers  SQL\Result_Seekable::rewind
	 *
	 * @dataProvider    provider_seek
	 *
	 * @param   integer $count
	 * @param   integer $position
	 */
	public function test_rewind($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		$result->seek($position);

		$this->assertSame($result, $result->rewind());
		$this->assertSame(0, $result->key());
	}

	/**
	 * @covers  SQL\Result_Seekable::prev
	 *
	 * @dataProvider    provider_seek
	 *
	 * @param   integer         $count
	 * @param   integer         $position
	 */
	public function test_prev($count, $position)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		$result->seek($position);

		for ($i = $position; $i >= 0; --$i)
		{
			$this->assertSame($i, $result->key());
			$this->assertSame($result, $result->prev());
		}

		$this->assertSame(-1, $result->key());
	}

	/**
	 * @covers  SQL\Result_Seekable::offsetExists
	 * 
	 * @dataProvider    provider_count
	 */
	public function test_offset_exists($count)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		for ($i = 0; $i < $count; ++$i)
		{
			$this->assertTrue($result->offsetExists($i));
		}

		$this->assertFalse($result->offsetExists(-1));
		$this->assertFalse($result->offsetExists($count));
	}

	/**
	 * Always throws an exception.
	 *
	 * @covers  SQL\Result_Seekable::offsetSet
	 */
	public function test_offset_set()
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array(1)
		);

		$this->setExpectedException('Exception');

		$result->offsetSet(0, TRUE);
	}

	/**
	 * Always throws an exception.
	 *
	 * @covers  SQL\Result_Seekable::offsetUnset
	 */
	public function test_offset_unset()
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array(1)
		);

		$this->setExpectedException('Exception');

		$result->offsetUnset(0);
	}

	/**
	 * @covers  SQL\Result_Seekable::valid
	 */
	public function test_valid_empty()
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array(0)
		);

		$this->assertFalse($result->valid());
	}

	public function provider_count_not_empty()
	{
		return array(
			array(1),
			array(2),
			array(3),
		);
	}

	/**
	 * @covers  SQL\Result_Seekable::valid
	 *
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_initial($count)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		$this->assertTrue($result->valid());
	}

	/**
	 * @covers  SQL\Result_Seekable::valid
	 *
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_low($count)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		// Move pointer before the beginning
		$result->prev();

		$this->assertFalse($result->valid());
	}

	/**
	 * @covers  SQL\Result_Seekable::valid
	 *
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_high($count)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		// Move pointer past the end
		for ($i = 0; $i < $count; ++$i)
		{
			$result->next();
		}

		$this->assertFalse($result->valid());
	}

	/**
	 * @covers  SQL\Result_Seekable::valid
	 *
	 * @dataProvider    provider_count_not_empty
	 */
	public function test_valid_end($count)
	{
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array($count)
		);

		// Move pointer to the end
		for ($i = 0; $i < ($count - 1); ++$i)
		{
			$result->next();
		}

		$this->assertTrue($result->valid());
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
	 * @covers  SQL\Result_Seekable::to_array
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
		$i = 0;
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array(count($rows))
		);

		foreach ($rows as $row)
		{
			$result->expects($this->at($i++))
				->method('current')
				->will($this->returnValue($row));
		}

		$this->assertSame($expected, $result->to_array($key, $value));
		$this->assertSame(count($rows), $result->key());
	}

	public function provider_to_array_after_seek()
	{
		$rows = array(
			array('id' => 5, 'value' => 50),
			array('id' => 6, 'value' => 60),
			array('id' => 7, 'value' => 70),
		);

		// data set #0
		$result[] = array(1, NULL, NULL, $rows, $rows);
		$result[] = array(2, NULL, NULL, $rows, $rows);

		// data set #2
		$result[] = array(1, NULL, 'id', $rows, array(5, 6, 7));
		$result[] = array(2, NULL, 'id', $rows, array(5, 6, 7));

		// data set #4
		$result[] = array(1, NULL, 'value', $rows, array(50, 60, 70));
		$result[] = array(2, NULL, 'value', $rows, array(50, 60, 70));

		// data set #6
		$result[] = array(
			1, 'id', 'value', $rows,
			array(5 => 50, 6 => 60, 7 => 70),
		);
		$result[] = array(
			2, 'id', 'value', $rows,
			array(5 => 50, 6 => 60, 7 => 70),
		);

		// data set #8
		$result[] = array(
			1, 'value', 'id', $rows,
			array(50 => 5, 60 => 6, 70 => 7),
		);
		$result[] = array(
			2, 'value', 'id', $rows,
			array(50 => 5, 60 => 6, 70 => 7),
		);

		// data set #10
		$result[] = array(
			1, 'id', NULL, $rows,
			array(
				5 => array('id' => 5, 'value' => 50),
				6 => array('id' => 6, 'value' => 60),
				7 => array('id' => 7, 'value' => 70),
			),
		);
		$result[] = array(
			2, 'id', NULL, $rows,
			array(
				5 => array('id' => 5, 'value' => 50),
				6 => array('id' => 6, 'value' => 60),
				7 => array('id' => 7, 'value' => 70),
			),
		);

		// data set #12
		$result[] = array(
			1, 'value', NULL, $rows,
			array(
				50 => array('id' => 5, 'value' => 50),
				60 => array('id' => 6, 'value' => 60),
				70 => array('id' => 7, 'value' => 70),
			),
		);
		$result[] = array(
			2, 'value', NULL, $rows,
			array(
				50 => array('id' => 5, 'value' => 50),
				60 => array('id' => 6, 'value' => 60),
				70 => array('id' => 7, 'value' => 70),
			),
		);

		return $result;
	}

	/**
	 * @covers  SQL\Result_Seekable::to_array
	 *
	 * @dataProvider    provider_to_array_after_seek
	 *
	 * @param   integer $position
	 * @param   string  $key        First argument to method
	 * @param   string  $value      Second argument to method
	 * @param   array   $rows       Data set
	 * @param   array   $expected
	 */
	public function test_to_array_after_seek($position, $key, $value, $rows, $expected)
	{
		$i = 0;
		$result = $this->getMockForAbstractClass(
			'SQL\Result_Seekable', array(count($rows))
		);

		foreach ($rows as $row)
		{
			$result->expects($this->at($i++))
				->method('current')
				->will($this->returnValue($row));
		}

		$result->seek($position);

		$this->assertEquals($expected, $result->to_array($key, $value));
		$this->assertSame(count($rows), $result->key());
	}
}
