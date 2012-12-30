<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @coversNothing
	 */
	public function test_interfaces()
	{
		$class = new \ReflectionClass('SQL\Result');

		$this->assertTrue($class->implementsInterface('Iterator'));
		$this->assertTrue($class->implementsInterface('Traversable'));
	}

	/**
	 * @covers  SQL\Result::key
	 * @covers  SQL\Result::next
	 */
	public function test_next()
	{
		$count = 3;
		$result = $this->getMockForAbstractClass('SQL\Result');

		for ($i = 0; $i < $count; ++$i)
		{
			$this->assertSame($i, $result->key());
			$this->assertSame($result, $result->next());
		}

		$this->assertSame($count, $result->key());
	}

	/**
	 * @covers  SQL\Result::key
	 * @covers  SQL\Result::rewind
	 */
	public function test_rewind()
	{
		$result = $this->getMockForAbstractClass('SQL\Result');

		$this->assertSame($result, $result->rewind());
		$this->assertSame(0, $result->key());

		$result->next();

		$this->assertSame($result, $result->rewind());
		$this->assertSame(1, $result->key());
	}

	public function provider_get()
	{
		return array(
			array(array('value' => 50), 'value', 50),
		);
	}

	/**
	 * @covers  SQL\Result::get
	 *
	 * @dataProvider    provider_get
	 *
	 * @param   array   $current    One row with a non-null value
	 * @param   string  $key        Index of the non-null value
	 * @param   mixed   $value      Non-null value
	 */
	public function test_get($current, $key, $value)
	{
		$result = $this->getMockForAbstractClass('SQL\Result');

		$result->expects($this->exactly(4))
			->method('current')
			->will($this->returnValue($current));
		$result->expects($this->exactly(4))
			->method('valid')
			->will($this->returnValue(TRUE));

		$this->assertSame($value, $result->get(), 'void');
		$this->assertSame(0, $result->key());

		$this->assertSame($value, $result->get($key), 'value');
		$this->assertSame(0, $result->key());

		$this->assertSame($value, $result->get($key, 'other'), 'default');
		$this->assertSame(0, $result->key());

		$this->assertSame(
			'other', $result->get($key.'non-existent', 'other'), 'non-existent'
		);
		$this->assertSame(0, $result->key());
	}

	/**
	 * @covers  SQL\Result::get
	 */
	public function test_get_invalid()
	{
		$result = $this->getMockForAbstractClass('SQL\Result');

		$result->expects($this->exactly(3))
			->method('valid')
			->will($this->returnValue(FALSE));

		$this->assertNull($result->get(), 'void');
		$this->assertSame(0, $result->key());

		$this->assertNull($result->get('value'), 'value');
		$this->assertSame(0, $result->key());

		$this->assertSame('other', $result->get('value', 'other'), 'default');
		$this->assertSame(0, $result->key());
	}

	public function provider_get_null()
	{
		return array(
			array(array('value' => NULL), 'value'),
		);
	}

	/**
	 * @covers  SQL\Result::get
	 *
	 * @dataProvider    provider_get_null
	 *
	 * @param   array   $current    One row with a NULL value
	 * @param   string  $key        Index of the NULL value
	 */
	public function test_get_null($current, $key)
	{
		$result = $this->getMockForAbstractClass('SQL\Result');

		$result->expects($this->exactly(3))
			->method('current')
			->will($this->returnValue($current));
		$result->expects($this->exactly(3))
			->method('valid')
			->will($this->returnValue(TRUE));

		$this->assertNull($result->get(), 'void');
		$this->assertSame(0, $result->key());

		$this->assertNull($result->get($key), 'value');
		$this->assertSame(0, $result->key());

		$this->assertSame('other', $result->get($key, 'other'), 'default');
		$this->assertSame(0, $result->key());
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
	 * @covers  SQL\Result::to_array
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
		$result = $this->getMockForAbstractClass('SQL\Result');

		foreach ($rows as $row)
		{
			$result->expects($this->at($i++))
				->method('valid')
				->will($this->returnValue(TRUE));
			$result->expects($this->at($i++))
				->method('current')
				->will($this->returnValue($row));
		}

		$result->expects($this->at($i++))
			->method('valid')
			->will($this->returnValue(FALSE));

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
		$result[] = array(
			1, NULL, NULL, $rows,
			array(
				array('id' => 6, 'value' => 60),
				array('id' => 7, 'value' => 70),
			),
		);
		$result[] = array(
			2, NULL, NULL, $rows,
			array(
				array('id' => 7, 'value' => 70),
			),
		);

		// data set #2
		$result[] = array(1, NULL, 'id', $rows, array(6, 7));
		$result[] = array(2, NULL, 'id', $rows, array(7));

		// data set #4
		$result[] = array(1, NULL, 'value', $rows, array(60, 70));
		$result[] = array(2, NULL, 'value', $rows, array(70));

		// data set #6
		$result[] = array(1, 'id', 'value', $rows, array(6 => 60, 7 => 70));
		$result[] = array(2, 'id', 'value', $rows, array(7 => 70));

		// data set #8
		$result[] = array(1, 'value', 'id', $rows, array(60 => 6, 70 => 7));
		$result[] = array(2, 'value', 'id', $rows, array(70 => 7));

		// data set #10
		$result[] = array(
			1, 'id', NULL, $rows,
			array(
				6 => array('id' => 6, 'value' => 60),
				7 => array('id' => 7, 'value' => 70),
			),
		);
		$result[] = array(
			2, 'id', NULL, $rows,
			array(
				7 => array('id' => 7, 'value' => 70),
			),
		);

		// data set #12
		$result[] = array(
			1, 'value', NULL, $rows,
			array(
				60 => array('id' => 6, 'value' => 60),
				70 => array('id' => 7, 'value' => 70),
			),
		);
		$result[] = array(
			2, 'value', NULL, $rows,
			array(
				70 => array('id' => 7, 'value' => 70),
			),
		);

		return $result;
	}

	/**
	 * @covers  SQL\Result::to_array
	 *
	 * @dataProvider    provider_to_array_after_seek
	 *
	 * @param   integer         $position
	 * @param   string          $key        First argument to method
	 * @param   string          $value      Second argument to method
	 * @param   array           $rows       Data set
	 * @param   array           $expected
	 */
	public function test_to_array_after_seek($position, $key, $value, $rows, $expected)
	{
		$i = 0;
		$result = $this->getMockForAbstractClass('SQL\Result');

		foreach ($rows as $j => $row)
		{
			if ($j < $position)
			{
				$result->next();
			}
			else
			{
				$result->expects($this->at($i++))
					->method('valid')
					->will($this->returnValue(TRUE));
				$result->expects($this->at($i++))
					->method('current')
					->will($this->returnValue($row));
			}
		}

		$result->expects($this->at($i++))
			->method('valid')
			->will($this->returnValue(FALSE));

		$this->assertSame($expected, $result->to_array($key, $value));
		$this->assertSame(count($rows), $result->key());
	}
}
