<?php
namespace SQL\DML;

use SQL\Alias;
use SQL\Column;
use SQL\Conditions;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;
use SQL\Table_Reference;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':from' => NULL,
		':groupby' => NULL,
		':having' => NULL,
		':limit' => NULL,
		':offset' => NULL,
		':orderby' => NULL,
		':values' => NULL,
		':where' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, 'SELECT *'),
			array(array(array('a')), array(new Column('a')), 'SELECT :values'),
			array(
				array(array('a', 'b')),
				array(new Column('a'), new Column('b')),
				'SELECT :values',
			),
		);
	}

	/**
	 * @covers  SQL\DML\Select::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $values     Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $values, $value)
	{
		$class = new \ReflectionClass('SQL\DML\Select');
		$select = $class->newInstanceArgs($arguments);

		$this->assertEquals($values, $select->values);

		$this->assertSame($value, (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$select->parameters
		);
	}

	public function provider_distinct()
	{
		return array(
			array(NULL, 'SELECT *'),
			array(FALSE, 'SELECT *'),
			array(TRUE, 'SELECT DISTINCT *'),
		);
	}

	/**
	 * @covers  SQL\DML\Select::distinct
	 *
	 * @dataProvider    provider_distinct
	 *
	 * @param   boolean $argument   Argument
	 * @param   string  $value
	 */
	public function test_distinct($argument, $value)
	{
		$select = new Select;

		$this->assertSame($select, $select->distinct($argument));
		$this->assertSame($argument, $select->distinct);

		$this->assertSame($value, (string) $select);
		$this->assertSame($this->parameters, $select->parameters);
	}

	/**
	 * @covers  SQL\DML\Select::distinct
	 */
	public function test_distinct_default()
	{
		$select = new Select;

		$this->assertSame($select, $select->distinct());
		$this->assertTrue($select->distinct);

		$this->assertSame('SELECT DISTINCT *', (string) $select);
		$this->assertSame($this->parameters, $select->parameters);
	}

	public function provider_column()
	{
		return array(
			array(array('a'), array(new Column('a'))),
			array(array('a', 'b'), array(new Alias(new Column('a'), 'b'))),
			array(array(new Column('a')), array(new Column('a'))),
			array(
				array(new Column('a'), 'b'),
				array(new Alias(new Column('a'), 'b')),
			),
			array(array(new Expression('a')), array(new Expression('a'))),
			array(
				array(new Expression('a'), 'b'),
				array(new Alias(new Expression('a'), 'b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Select::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $values     Expected property
	 */
	public function test_column($arguments, $values)
	{
		$select = new Select;

		$this->assertSame(
			$select, call_user_func_array(array($select, 'column'), $arguments)
		);
		$this->assertEquals($values, $select->values);

		$this->assertSame('SELECT :values', (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$select->parameters
		);
	}

	public function provider_columns()
	{
		return array(
			array(NULL, NULL, 'SELECT *'),
			array(array('a'), array(new Column('a')), 'SELECT :values'),
			array(
				array('a', 'b'), array(new Column('a'), new Column('b')),
				'SELECT :values',
			),
			array(
				array('a' => 'b'), array(new Alias(new Column('b'), 'a')),
				'SELECT :values',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'SELECT :values',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'SELECT :values',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'SELECT :values',
			),
			array(
				array('a' => new Column('b')),
				array(new Alias(new Column('b'), 'a')),
				'SELECT :values',
			),
			array(
				array('a' => new Column('b'), 'c' => new Column('d')),
				array(
					new Alias(new Column('b'), 'a'),
					new Alias(new Column('d'), 'c'),
				),
				'SELECT :values',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'SELECT :values',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'SELECT :values',
			),
			array(
				array('a' => new Expression('b')),
				array(new Alias(new Expression('b'), 'a')),
				'SELECT :values',
			),
			array(
				array('a' => new Expression('b'), 'c' => new Expression('d')),
				array(
					new Alias(new Expression('b'), 'a'),
					new Alias(new Expression('d'), 'c'),
				),
				'SELECT :values',
			),
		);
	}

	/**
	 * @covers  SQL\DML\Select::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $argument   Argument
	 * @param   array   $values     Expected property
	 * @param   string  $value
	 */
	public function test_columns($argument, $values, $value)
	{
		$select = new Select;

		$this->assertSame($select, $select->columns($argument));
		$this->assertEquals($values, $select->values);

		$this->assertSame($value, (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Select::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_columns_reset($argument)
	{
		$select = new Select;
		$select->columns($argument);

		$this->assertSame($select, $select->columns(NULL));
		$this->assertNull($select->values);

		$this->assertSame('SELECT *', (string) $select);
		$this->assertSame($this->parameters, $select->parameters);
	}

	public function provider_value()
	{
		$result[] = array(array(NULL), array(NULL));
		$result[] = array(array(NULL, 'a'), array(new Alias(NULL, 'a')));

		$result[] = array(array(0), array(0));
		$result[] = array(array(0, 'b'), array(new Alias(0, 'b')));

		$result[] = array(array(1), array(1));
		$result[] = array(array(1, 'c'), array(new Alias(1, 'c')));

		$result[] = array(array('d'), array('d'));
		$result[] = array(array('e', 'f'), array(new Alias('e', 'f')));

		$result[] = array(
			array(new Expression('g')), array(new Expression('g')),
		);
		$result[] = array(
			array(new Expression('h'), 'i'),
			array(new Alias(new Expression('h'), 'i')),
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::value
	 *
	 * @dataProvider    provider_value
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $values     Expected property
	 */
	public function test_value($arguments, $values)
	{
		$select = new Select;

		$this->assertSame(
			$select, call_user_func_array(array($select, 'value'), $arguments)
		);
		$this->assertEquals($values, $select->values);

		$this->assertSame('SELECT :values', (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$select->parameters
		);
	}

	public function provider_values()
	{
		return array(
			array(NULL, NULL, 'SELECT *'),
			array(array(0), array(0), 'SELECT :values'),
			array(array(0, 1), array(0, 1), 'SELECT :values'),
			array(array(0, 1, 'a'), array(0, 1, 'a'), 'SELECT :values'),
			array(array(0, 1, 'a', 'b'), array(0, 1, 'a', 'b'), 'SELECT :values'),

			array(array('a' => 0), array(new Alias(0, 'a')), 'SELECT :values'),
			array(
				array('a' => 0, 'b' => 'c'),
				array(new Alias(0, 'a'), new Alias('c', 'b')),
				'SELECT :values',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'SELECT :values',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'SELECT :values',
			),
			array(
				array('a' => new Expression('b')),
				array(new Alias(new Expression('b'), 'a')),
				'SELECT :values',
			),
			array(
				array('a' => new Expression('b'), 'c' => new Expression('d')),
				array(
					new Alias(new Expression('b'), 'a'),
					new Alias(new Expression('d'), 'c'),
				),
				'SELECT :values',
			),
		);
	}

	/**
	 * @covers  SQL\DML\Select::values
	 *
	 * @dataProvider    provider_values
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $values     Expected property
	 * @param   string  $value
	 */
	public function test_values($argument, $values, $value)
	{
		$select = new Select;

		$this->assertSame($select, $select->values($argument));
		$this->assertEquals($values, $select->values);

		$this->assertSame($value, (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Select::values
	 *
	 * @dataProvider    provider_values
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_values_reset($argument)
	{
		$select = new Select;
		$select->values($argument);

		$this->assertSame($select, $select->values(NULL));
		$this->assertNull($select->values);

		$this->assertSame('SELECT *', (string) $select);
		$this->assertSame($this->parameters, $select->parameters);
	}

	public function provider_from()
	{
		$result[] = array(array('a'), new Table_Reference('a'));
		$result[] = array(array('a', 'b'), new Table_Reference('a', 'b'));

		$reference = new Table_Reference('a');
		$result[] = array(array($reference), $reference);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::from
	 *
	 * @dataProvider    provider_from
	 *
	 * @param   array           $arguments  Arguments
	 * @param   Table_Reference $from       Expected
	 */
	public function test_from($arguments, $from)
	{
		$select = new Select;

		$this->assertSame(
			$select, call_user_func_array(array($select, 'from'), $arguments)
		);
		$this->assertEquals($from, $select->from);

		$this->assertSame('SELECT * FROM :from', (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':from' => $from)),
			$select->parameters
		);
	}

	public function provider_where()
	{
		$conditions = new Conditions;
		$result[] = array(array($conditions), $conditions);

		$expression = new Expression('');
		$result[] = array(array($expression), $expression);

		$result[] = array(
			array('a', '=', 'b'), new Conditions(new Column('a'), '=', 'b'),
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Conditions  $where      Expected
	 */
	public function test_where($arguments, $where)
	{
		$select = new Select;

		$this->assertSame(
			$select, call_user_func_array(array($select, 'where'), $arguments)
		);
		$this->assertEquals($where, $select->where);

		$this->assertSame('SELECT * WHERE :where', (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':where' => $where)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Select::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_where_reset($arguments)
	{
		$select = new Select;
		call_user_func_array(array($select, 'where'), $arguments);

		$this->assertSame($select, $select->where(NULL));
		$this->assertNull($select->where);

		$this->assertSame('SELECT *', (string) $select);
		$this->assertSame($this->parameters, $select->parameters);
	}

	public function provider_group_by()
	{
		return array(
			array(NULL, NULL, 'SELECT *'),
			array(
				array('a'), array(new Column('a')),
				'SELECT * GROUP BY :groupby',
			),
			array(
				array('a', 'b'), array(new Column('a'), new Column('b')),
				'SELECT * GROUP BY :groupby',
			),
			array(
				array(new Column('a')), array(new Column('a')),
				'SELECT * GROUP BY :groupby',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'SELECT * GROUP BY :groupby',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'SELECT * GROUP BY :groupby',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'SELECT * GROUP BY :groupby',
			),
		);
	}

	/**
	 * @covers  SQL\DML\Select::group_by
	 *
	 * @dataProvider    provider_group_by
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $group_by   Expected property
	 * @param   string  $value
	 */
	public function test_group_by($argument, $group_by, $value)
	{
		$select = new Select;

		$this->assertSame($select, $select->group_by($argument));
		$this->assertEquals($group_by, $select->group_by);

		$this->assertSame($value, (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':groupby' => $group_by)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Select::group_by
	 *
	 * @dataProvider    provider_group_by
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_group_by_reset($argument)
	{
		$select = new Select;
		$select->group_by($argument);

		$this->assertSame($select, $select->group_by(NULL));
		$this->assertNull($select->group_by);

		$this->assertSame('SELECT *', (string) $select);
		$this->assertSame($this->parameters, $select->parameters);
	}

	/**
	 * @covers  SQL\DML\Select::having
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Conditions  $having     Expected
	 */
	public function test_having($arguments, $having)
	{
		$select = new Select;

		$this->assertSame(
			$select, call_user_func_array(array($select, 'having'), $arguments)
		);
		$this->assertEquals($having, $select->having);

		$this->assertSame('SELECT * HAVING :having', (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':having' => $having)),
			$select->parameters
		);
	}

	public function provider_order_by()
	{
		return array(
			array(array(NULL), NULL, 'SELECT *'),
			array(array(NULL, 'any'), NULL, 'SELECT *'),
			array(array(NULL, new Expression('any')), NULL, 'SELECT *'),

			array(
				array('a'), array(new Column('a')),
				'SELECT * ORDER BY :orderby',
			),
			array(
				array('a', 'b'),
				array(new Expression('? B', array(new Column('a')))),
				'SELECT * ORDER BY :orderby',
			),
			array(
				array('a', new Expression('b')),
				array(new Expression('? ?', array(
					new Column('a'), new Expression('b'))
				)),
				'SELECT * ORDER BY :orderby',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'SELECT * ORDER BY :orderby',
			),
			array(
				array(new Column('a'), 'b'),
				array(new Expression('? B', array(new Column('a')))),
				'SELECT * ORDER BY :orderby',
			),
			array(
				array(new Column('a'),
				new Expression('b')), array(new Expression('? ?', array(
					new Column('a'), new Expression('b'))
				)),
				'SELECT * ORDER BY :orderby',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'SELECT * ORDER BY :orderby',
			),
			array(
				array(new Expression('a'), 'b'),
				array(new Expression('? B', array(new Expression('a')))),
				'SELECT * ORDER BY :orderby',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('? ?', array(
					new Expression('a'), new Expression('b'))
				)),
				'SELECT * ORDER BY :orderby',
			),
		);
	}

	/**
	 * @covers  SQL\DML\Select::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $order_by   Expected property
	 * @param   string  $value
	 */
	public function test_order_by($arguments, $order_by, $value)
	{
		$select = new Select;

		$this->assertSame(
			$select,
			call_user_func_array(array($select, 'order_by'), $arguments)
		);
		$this->assertEquals($order_by, $select->order_by);

		$this->assertSame($value, (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':orderby' => $order_by)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Select::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_order_by_reset($arguments)
	{
		$select = new Select;

		call_user_func_array(array($select, 'order_by'), $arguments);

		$this->assertSame($select, $select->order_by(NULL));
		$this->assertNull($select->order_by);

		$this->assertSame('SELECT *', (string) $select);
		$this->assertSame($this->parameters, $select->parameters);
	}

	public function provider_limit()
	{
		return array(
			array(NULL, 'SELECT *'),

			array(0, 'SELECT * LIMIT :limit'),
			array(1, 'SELECT * LIMIT :limit'),
		);
	}

	/**
	 * @covers  SQL\DML\Select::limit
	 *
	 * @dataProvider    provider_limit
	 *
	 * @param   integer $argument   Argument
	 * @param   string  $value
	 */
	public function test_limit($argument, $value)
	{
		$select = new Select;

		$this->assertSame($select, $select->limit($argument));
		$this->assertSame($argument, $select->limit);

		$this->assertSame($value, (string) $select);
		$this->assertSame(
			array_merge($this->parameters, array(':limit' => $argument)),
			$select->parameters
		);
	}

	public function provider_offset()
	{
		return array(
			array(NULL, 'SELECT *'),
			array(0, 'SELECT *'),

			array(1, 'SELECT * OFFSET :offset'),
		);
	}

	/**
	 * @covers  SQL\DML\Select::offset
	 *
	 * @dataProvider    provider_offset
	 *
	 * @param   integer $argument   Argument
	 * @param   string  $value
	 */
	public function test_offset($argument, $value)
	{
		$select = new Select;

		$this->assertSame($select, $select->offset($argument));
		$this->assertSame($argument, $select->offset);

		$this->assertSame($value, (string) $select);
		$this->assertSame(
			array_merge($this->parameters, array(':offset' => $argument)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Select::__toString
	 */
	public function test_toString()
	{
		$select = new Select;
		$select
			->distinct()
			->from('a')
			->where('b', '=', 'c')
			->group_by(array('d'))
			->having('e', '=', 'f')
			->order_by('g')
			->limit(1)
			->offset(1);

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $select
		);

		$select->value('h');

		$this->assertSame(
			'SELECT DISTINCT :values FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $select
		);
	}
}
