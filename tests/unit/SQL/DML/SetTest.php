<?php
namespace SQL\DML;

use SQL\Column;
use SQL\Expression;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class SetTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':limit' => NULL,
		':offset' => NULL,
		':orderby' => NULL,
	);

	/**
	 * @covers  SQL\DML\Set::__construct
	 */
	public function test_constructor()
	{
		$set = new Set;

		$this->assertSame('', (string) $set);
		$this->assertSame($this->parameters, $set->parameters);

		$set = new Set(new Expression('a'));

		$this->assertSame('(?)', (string) $set);
		$this->assertEquals(
			array_merge($this->parameters, array(new Expression('a'))),
			$set->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Set::add
	 */
	public function test_add()
	{
		$set = new Set;

		$this->assertSame($set, $set->add('a', new Expression('b')));
		$this->assertSame('(?)', (string) $set);
		$this->assertEquals(
			array_merge($this->parameters, array(new Expression('b'))),
			$set->parameters
		);

		$this->assertSame($set, $set->add('c', new Expression('d')));
		$this->assertSame('(?) C (?)', (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('b'), new Expression('d'))
			),
			$set->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Set::open
	 */
	public function test_open()
	{
		$set = new Set;

		$this->assertSame($set, $set->open('a'));
		$this->assertSame('(', (string) $set);
		$this->assertSame($this->parameters, $set->parameters);

		$this->assertSame($set, $set->open('b', new Expression('c')));
		$this->assertSame('(((?)', (string) $set);
		$this->assertEquals(
			array_merge($this->parameters, array(new Expression('c'))),
			$set->parameters
		);

		$this->assertSame($set, $set->open('d', new Expression('e')));
		$this->assertSame('(((?) D ((?)', (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('c'), new Expression('e'))
			),
			$set->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Set::close
	 */
	public function test_close()
	{
		$set = new Set;

		$this->assertSame($set, $set->close());
		$this->assertSame(')', (string) $set);
		$this->assertSame($this->parameters, $set->parameters);

		$this->assertSame($set, $set->close());
		$this->assertSame('))', (string) $set);
		$this->assertSame($this->parameters, $set->parameters);
	}

	/**
	 * @covers  SQL\DML\Set::close
	 * @covers  SQL\DML\Set::open
	 */
	public function test_close_open()
	{
		$set = new Set;

		$this->assertSame(') A (',      (string) $set->close()->open('a'));
		$this->assertSame(') A () B (', (string) $set->close()->open('b'));
	}

	public function provider_except()
	{
		return array(
			array(
				array(new Expression('b')),
				'(?) EXCEPT (?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), FALSE),
				'(?) EXCEPT (?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), TRUE),
				'(?) EXCEPT ALL (?)', array(new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Set::except
	 *
	 * @dataProvider    provider_except
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $value      Expected
	 * @param   array   $parameters Expected
	 */
	public function test_except($arguments, $value, $parameters)
	{
		$set = new Set(new Expression('a'));

		$result = call_user_func_array(array($set, 'except'), $arguments);

		$this->assertSame($set, $result);
		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				$parameters
			),
			$set->parameters
		);
	}

	public function provider_except_open()
	{
		return array(
			array(array(), '(?) EXCEPT (', array()),
			array(array(NULL), '(?) EXCEPT (', array()),
			array(array(NULL, FALSE), '(?) EXCEPT (', array()),
			array(array(NULL, TRUE), '(?) EXCEPT ALL (', array()),

			array(
				array(new Expression('b')),
				'(?) EXCEPT ((?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), FALSE),
				'(?) EXCEPT ((?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), TRUE),
				'(?) EXCEPT ALL ((?)', array(new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Set::except_open
	 *
	 * @dataProvider    provider_except_open
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $value      Expected
	 * @param   array   $parameters Expected
	 */
	public function test_except_open($arguments, $value, $parameters)
	{
		$set = new Set(new Expression('a'));

		$result = call_user_func_array(array($set, 'except_open'), $arguments);

		$this->assertSame($set, $result);
		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				$parameters
			),
			$set->parameters
		);
	}

	public function provider_intersect()
	{
		return array(
			array(
				array(new Expression('b')),
				'(?) INTERSECT (?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), FALSE),
				'(?) INTERSECT (?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), TRUE),
				'(?) INTERSECT ALL (?)', array(new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Set::intersect
	 *
	 * @dataProvider    provider_intersect
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $value      Expected
	 * @param   array   $parameters Expected
	 */
	public function test_intersect($arguments, $value, $parameters)
	{
		$set = new Set(new Expression('a'));

		$result = call_user_func_array(array($set, 'intersect'), $arguments);

		$this->assertSame($set, $result);
		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				$parameters
			),
			$set->parameters
		);
	}

	public function provider_intersect_open()
	{
		return array(
			array(array(), '(?) INTERSECT (', array()),
			array(array(NULL), '(?) INTERSECT (', array()),
			array(array(NULL, FALSE), '(?) INTERSECT (', array()),
			array(array(NULL, TRUE), '(?) INTERSECT ALL (', array()),

			array(
				array(new Expression('b')),
				'(?) INTERSECT ((?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), FALSE),
				'(?) INTERSECT ((?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), TRUE),
				'(?) INTERSECT ALL ((?)', array(new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Set::intersect_open
	 *
	 * @dataProvider    provider_intersect_open
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $value      Expected
	 * @param   array   $parameters Expected
	 */
	public function test_intersect_open($arguments, $value, $parameters)
	{
		$set = new Set(new Expression('a'));

		$result = call_user_func_array(
			array($set, 'intersect_open'), $arguments
		);

		$this->assertSame($set, $result);
		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				$parameters
			),
			$set->parameters
		);
	}

	public function provider_union()
	{
		return array(
			array(
				array(new Expression('b')),
				'(?) UNION (?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), FALSE),
				'(?) UNION (?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), TRUE),
				'(?) UNION ALL (?)', array(new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Set::union
	 *
	 * @dataProvider    provider_union
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $value      Expected
	 * @param   array   $parameters Expected
	 */
	public function test_union($arguments, $value, $parameters)
	{
		$set = new Set(new Expression('a'));

		$result = call_user_func_array(array($set, 'union'), $arguments);

		$this->assertSame($set, $result);
		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				$parameters
			),
			$set->parameters
		);
	}

	public function provider_union_open()
	{
		return array(
			array(array(), '(?) UNION (', array()),
			array(array(NULL), '(?) UNION (', array()),
			array(array(NULL, FALSE), '(?) UNION (', array()),
			array(array(NULL, TRUE), '(?) UNION ALL (', array()),

			array(
				array(new Expression('b')),
				'(?) UNION ((?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), FALSE),
				'(?) UNION ((?)', array(new Expression('b')),
			),
			array(
				array(new Expression('b'), TRUE),
				'(?) UNION ALL ((?)', array(new Expression('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Set::union_open
	 *
	 * @dataProvider    provider_union_open
	 *
	 * @param   array   $arguments  Arguments to the method
	 * @param   string  $value      Expected
	 * @param   array   $parameters Expected
	 */
	public function test_union_open($arguments, $value, $parameters)
	{
		$set = new Set(new Expression('a'));

		$result = call_user_func_array(array($set, 'union_open'), $arguments);

		$this->assertSame($set, $result);
		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				$parameters
			),
			$set->parameters
		);
	}

	public function provider_order_by()
	{
		$result[] = array(array(NULL), NULL, '(?)', $this->parameters);
		$result[] = array(
			array(NULL, 'any'), NULL,
			'(?)', $this->parameters,
		);
		$result[] = array(
			array(NULL, new Expression('any')), NULL,
			'(?)', $this->parameters,
		);

		$order_by = array(new Column('a'));
		$result[] = array(
			array('a'), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(new Expression('? B', array(new Column('a'))));
		$result[] = array(
			array('a', 'b'), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(
			new Expression('? ?', array(new Column('a'), new Expression('b'))),
		);
		$result[] = array(
			array('a', new Expression('b')), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(new Column('a'));
		$result[] = array(
			array(new Column('a')), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(new Expression('? B', array(new Column('a'))));
		$result[] = array(
			array(new Column('a'), 'b'), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(
			new Expression('? ?', array(new Column('a'), new Expression('b'))),
		);
		$result[] = array(
			array(new Column('a'), new Expression('b')), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(new Expression('a'));
		$result[] = array(
			array(new Expression('a')), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(new Expression('? B', array(new Expression('a'))));
		$result[] = array(
			array(new Expression('a'), 'b'), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		$order_by = array(
			new Expression(
				'? ?', array(new Expression('a'), new Expression('b'))
			),
		);
		$result[] = array(
			array(new Expression('a'), new Expression('b')), $order_by,
			'(?) ORDER BY :orderby', array(':orderby' => $order_by),
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Set::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $order_by   Expected
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_order_by($arguments, $order_by, $value, $parameters)
	{
		$set = new Set(new Expression('a'));

		$this->assertSame(
			$set, call_user_func_array(array($set, 'order_by'), $arguments)
		);
		$this->assertEquals($order_by, $set->order_by);

		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters, array(new Expression('a')), $parameters
			),
			$set->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Set::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_order_by_reset($arguments)
	{
		$set = new Set(new Expression('a'));

		call_user_func_array(array($set, 'order_by'), $arguments);

		$this->assertSame($set, $set->order_by(NULL));
		$this->assertNull($set->order_by);

		$this->assertSame('(?)', (string) $set);
		$this->assertEquals(
			array_merge($this->parameters, array(new Expression('a'))),
			$set->parameters
		);
	}

	public function provider_limit()
	{
		return array(
			array(NULL, '(?)'),

			array(0, '(?) LIMIT :limit'),
			array(1, '(?) LIMIT :limit'),
		);
	}

	/**
	 * @covers  SQL\DML\Set::limit
	 *
	 * @dataProvider    provider_limit
	 *
	 * @param   integer $argument   Argument
	 * @param   string  $value
	 */
	public function test_limit($argument, $value)
	{
		$set = new Set(new Expression('a'));

		$this->assertSame($set, $set->limit($argument));
		$this->assertSame($argument, $set->limit);

		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				array(':limit' => $argument)
			),
			$set->parameters
		);
	}

	public function provider_offset()
	{
		return array(
			array(NULL, '(?)'),
			array(0, '(?)'),

			array(1, '(?) OFFSET :offset'),
		);
	}

	/**
	 * @covers  SQL\DML\Set::offset
	 *
	 * @dataProvider    provider_offset
	 *
	 * @param   integer $argument   Argument
	 * @param   string  $value
	 */
	public function test_offset($argument, $value)
	{
		$set = new Set(new Expression('a'));

		$this->assertSame($set, $set->offset($argument));
		$this->assertSame($argument, $set->offset);

		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(new Expression('a')),
				array(':offset' => $argument)
			),
			$set->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Set::__toString
	 */
	public function test_toString()
	{
		$set = new Set(new Expression('a'));
		$set
			->order_by('b')
			->limit(1)
			->offset(1);

		$this->assertSame(
			'(?) ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $set
		);
	}
}
