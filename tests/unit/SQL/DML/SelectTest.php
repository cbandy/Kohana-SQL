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
			array(array(), 'SELECT *', $this->parameters),
			array(
				array(array('a')),
				'SELECT :values',
				array_merge(
					$this->parameters,
					array(':values' => array(new Column('a')))
				),
			),
			array(
				array(array('a', 'b')),
				'SELECT :values',
				array_merge(
					$this->parameters,
					array(':values' => array(new Column('a'), new Column('b')))
				),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Select::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_constructor($arguments, $value, $parameters)
	{
		$class = new \ReflectionClass('SQL\DML\Select');
		$select = $class->newInstanceArgs($arguments);

		$this->assertSame($value, (string) $select);
		$this->assertEquals($parameters, $select->parameters);
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

	public function provider_column()
	{
		$values = array(new Column('a'));
		$result[] = array(
			array('a'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(new Column('a'), 'b'));
		$result[] = array(
			array('a', 'b'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Column('a'));
		$result[] = array(
			array(new Column('a')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(new Column('a'), 'b'));
		$result[] = array(
			array(new Column('a'), 'b'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Expression('a'));
		$result[] = array(
			array(new Expression('a')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(new Expression('a'), 'b'));
		$result[] = array(
			array(new Expression('a'), 'b'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::column
	 *
	 * @dataProvider    provider_column
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $values     Expected property
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_column($arguments, $values, $value, $parameters)
	{
		$select = new Select;

		$this->assertSame(
			$select, call_user_func_array(array($select, 'column'), $arguments)
		);

		$this->assertEquals($values, $select->values);

		$this->assertSame($value, (string) $select);
		$this->assertEquals($parameters, $select->parameters);
	}

	public function provider_columns()
	{
		$result[] = array(NULL, NULL, 'SELECT *', $this->parameters);

		$values = array(new Column('a'));
		$result[] = array(
			array('a'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Column('a'), new Column('b'));
		$result[] = array(
			array('a', 'b'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(new Column('b'), 'a'));
		$result[] = array(
			array('a' => 'b'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(
			new Alias(new Column('b'), 'a'),
			new Alias(new Column('d'), 'c'),
		);
		$result[] = array(
			array('a' => 'b', 'c' => 'd'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Column('a'));
		$result[] = array(
			array(new Column('a')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Column('a'), new Column('b'));
		$result[] = array(
			array(new Column('a'), new Column('b')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(new Column('b'), 'a'));
		$result[] = array(
			array('a' => new Column('b')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(
			new Alias(new Column('b'), 'a'),
			new Alias(new Column('d'), 'c'),
		);
		$result[] = array(
			array('a' => new Column('b'), 'c' => new Column('d')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Expression('a'));
		$result[] = array(
			array(new Expression('a')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Expression('a'), new Expression('b'));
		$result[] = array(
			array(new Expression('a'), new Expression('b')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(new Expression('b'), 'a'));
		$result[] = array(
			array('a' => new Expression('b')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(
			new Alias(new Expression('b'), 'a'),
			new Alias(new Expression('d'), 'c'),
		);
		$result[] = array(
			array('a' => new Expression('b'), 'c' => new Expression('d')),
			$values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::columns
	 *
	 * @dataProvider    provider_columns
	 *
	 * @param   mixed   $argument   Argument
	 * @param   array   $values
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_columns($argument, $values, $value, $parameters)
	{
		$select = new Select;

		$this->assertSame($select, $select->columns($argument));
		$this->assertEquals($values, $select->values);

		$this->assertSame($value, (string) $select);
		$this->assertEquals($parameters, $select->parameters);
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
	 * @param   array   $values
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
			array(':values' => $values) + $this->parameters,
			$select->parameters
		);
	}

	public function provider_values()
	{
		$result[] = array(NULL, NULL, 'SELECT *', $this->parameters);

		$values = array(0);
		$result[] = array(
			array(0), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(0, 1);
		$result[] = array(
			array(0, 1), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(0, 1, 'a');
		$result[] = array(
			array(0, 1, 'a'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(0, 1, 'a', 'b');
		$result[] = array(
			array(0, 1, 'a', 'b'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(0, 'a'));
		$result[] = array(
			array('a' => 0), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(0, 'a'), new Alias('c', 'b'));
		$result[] = array(
			array('a' => 0, 'b' => 'c'), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Expression('a'));
		$result[] = array(
			array(new Expression('a')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Expression('a'), new Expression('b'));
		$result[] = array(
			array(new Expression('a'), new Expression('b')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(new Alias(new Expression('b'), 'a'));
		$result[] = array(
			array('a' => new Expression('b')), $values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		$values = array(
			new Alias(new Expression('b'), 'a'),
			new Alias(new Expression('d'), 'c'),
		);
		$result[] = array(
			array('a' => new Expression('b'), 'c' => new Expression('d')),
			$values,
			'SELECT :values', array(':values' => $values) + $this->parameters,
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::values
	 *
	 * @dataProvider    provider_values
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $values
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_values($argument, $values, $value, $parameters)
	{
		$select = new Select;

		$this->assertSame($select, $select->values($argument));
		$this->assertEquals($values, $select->values);

		$this->assertSame($value, (string) $select);
		$this->assertEquals($parameters, $select->parameters);
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
			array(':from' => $from) + $this->parameters, $select->parameters
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
			array(':where' => $where) + $this->parameters, $select->parameters
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
		$result[] = array(NULL, NULL, 'SELECT *', $this->parameters);

		$group_by = array(new Column('a'));
		$result[] = array(
			array('a'), $group_by,
			'SELECT * GROUP BY :groupby',
			array(':groupby' => $group_by) + $this->parameters,
		);

		$group_by = array(new Column('a'), new Column('b'));
		$result[] = array(
			array('a', 'b'), $group_by,
			'SELECT * GROUP BY :groupby',
			array(':groupby' => $group_by) + $this->parameters,
		);

		$group_by = array(new Column('a'));
		$result[] = array(
			array(new Column('a')), $group_by,
			'SELECT * GROUP BY :groupby',
			array(':groupby' => $group_by) + $this->parameters,
		);

		$group_by = array(new Column('a'), new Column('b'));
		$result[] = array(
			array(new Column('a'), new Column('b')), $group_by,
			'SELECT * GROUP BY :groupby',
			array(':groupby' => $group_by) + $this->parameters,
		);

		$group_by = array(new Expression('a'));
		$result[] = array(
			array(new Expression('a')), $group_by,
			'SELECT * GROUP BY :groupby',
			array(':groupby' => $group_by) + $this->parameters,
		);

		$group_by = array(new Expression('a'), new Expression('b'));
		$result[] = array(
			array(new Expression('a'), new Expression('b')), $group_by,
			'SELECT * GROUP BY :groupby',
			array(':groupby' => $group_by) + $this->parameters,
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::group_by
	 *
	 * @dataProvider    provider_group_by
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $group_by   Expected
	 * @param   string  $value
	 * @param   array   $parameters
	 */
	public function test_group_by($argument, $group_by, $value, $parameters)
	{
		$select = new Select;

		$this->assertSame($select, $select->group_by($argument));
		$this->assertEquals($group_by, $select->group_by);

		$this->assertSame($value, (string) $select);
		$this->assertEquals($parameters, $select->parameters);
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
			array(':having' => $having) + $this->parameters, $select->parameters
		);
	}

	public function provider_order_by()
	{
		$result[] = array(array(NULL), NULL, 'SELECT *', $this->parameters);
		$result[] = array(
			array(NULL, 'any'), NULL,
			'SELECT *', $this->parameters,
		);
		$result[] = array(
			array(NULL, new Expression('any')), NULL,
			'SELECT *', $this->parameters,
		);

		$order_by = array(new Column('a'));
		$result[] = array(
			array('a'), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(new Expression('? B', array(new Column('a'))));
		$result[] = array(
			array('a', 'b'), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(
			new Expression('? ?', array(new Column('a'), new Expression('b'))),
		);
		$result[] = array(
			array('a', new Expression('b')), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(new Column('a'));
		$result[] = array(
			array(new Column('a')), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(new Expression('? B', array(new Column('a'))));
		$result[] = array(
			array(new Column('a'), 'b'), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(
			new Expression('? ?', array(new Column('a'), new Expression('b'))),
		);
		$result[] = array(
			array(new Column('a'), new Expression('b')), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(new Expression('a'));
		$result[] = array(
			array(new Expression('a')), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(new Expression('? B', array(new Expression('a'))));
		$result[] = array(
			array(new Expression('a'), 'b'), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		$order_by = array(
			new Expression(
				'? ?', array(new Expression('a'), new Expression('b'))
			),
		);
		$result[] = array(
			array(new Expression('a'), new Expression('b')), $order_by,
			'SELECT * ORDER BY :orderby',
			array(':orderby' => $order_by) + $this->parameters,
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Select::order_by
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
		$select = new Select;

		$this->assertSame(
			$select,
			call_user_func_array(array($select, 'order_by'), $arguments)
		);
		$this->assertEquals($order_by, $select->order_by);

		$this->assertSame($value, (string) $select);
		$this->assertEquals($parameters, $select->parameters);
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
