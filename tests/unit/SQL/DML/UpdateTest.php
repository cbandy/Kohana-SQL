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
class UpdateTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':from' => NULL,
		':limit' => NULL,
		':table' => NULL,
		':values' => NULL,
		':where' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, NULL),

			array(array('a'), new Table('a'), NULL),
			array(array('a', 'b'), new Alias(new Table('a'), 'b'), NULL),

			array(
				array(NULL, NULL, array('a' => 'b')),
				NULL,
				array(new Expression('? = ?', array(new Column('a'), 'b'))),
			),

			array(
				array('a', 'b', array('c' => 'd')),
				new Alias(new Table('a'), 'b'),
				array(new Expression('? = ?', array(new Column('c'), 'd'))),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Update::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array           $arguments  Arguments
	 * @param   Table_Reference $table      Expected property
	 * @param   array           $values     Expected property
	 */
	public function test_constructor($arguments, $table, $values)
	{
		$class = new \ReflectionClass('SQL\DML\Update');
		$update = $class->newInstanceArgs($arguments);

		$this->assertEquals($table, $update->table);
		$this->assertEquals($values, $update->values);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertEquals(
			array_merge(
				$this->parameters,
				array(':table' => $table, ':values' => $values)
			),
			$update->parameters
		);
	}

	public function provider_from()
	{
		// data set #0
		$result[] = array(array('a'), new Table_Reference('a'));
		$result[] = array(array(array('a')), new Table_Reference('a'));

		$table = new Expression('a');
		$result[] = array(array($table), new Table_Reference($table));

		$table = new Identifier('a');
		$result[] = array(array($table), new Table_Reference($table));

		$reference = new Table_Reference('a');
		$result[] = array(array($reference), $reference);

		// data set #5
		$result[] = array(array('a', 'b'), new Table_Reference('a', 'b'));
		$result[] = array(array('a', array('b')), new Table_Reference('a', 'b'));

		$alias = new Expression('b');
		$result[] = array(array('a', $alias), new Table_Reference('a', $alias));

		$alias = new Identifier('b');
		$result[] = array(array('a', $alias), new Table_Reference('a', $alias));

		$reference = new Table_Reference('a');
		$result[] = array(array($reference, 'ignored'), $reference);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Update::from
	 *
	 * @dataProvider    provider_from
	 *
	 * @param   array           $arguments  Arguments
	 * @param   Table_Reference $from       Expected
	 */
	public function test_from($arguments, $from)
	{
		$update = new Update;

		$this->assertSame(
			$update, call_user_func_array(array($update, 'from'), $arguments)
		);
		$this->assertEquals($from, $update->from);

		$this->assertSame(
			'UPDATE :table SET :values FROM :from', (string) $update
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':from' => $from)),
			$update->parameters
		);
	}

	public function provider_limit()
	{
		return array(
			array(NULL, 'UPDATE :table SET :values'),

			array(0, 'UPDATE :table SET :values LIMIT :limit'),
			array(1, 'UPDATE :table SET :values LIMIT :limit'),
		);
	}

	/**
	 * @covers  SQL\DML\Update::limit
	 *
	 * @dataProvider    provider_limit
	 *
	 * @param   integer $argument   Argument
	 * @param   string  $value
	 */
	public function test_limit($argument, $value)
	{
		$update = new Update;

		$this->assertSame($update, $update->limit($argument));
		$this->assertSame($argument, $update->limit);

		$this->assertSame($value, (string) $update);
		$this->assertSame(
			array_merge($this->parameters, array(':limit' => $argument)),
			$update->parameters
		);
	}

	public function provider_set()
	{
		// data set #0
		$result[] = array(NULL, NULL);

		$result[] = array(
			array('a' => 'b'),
			array(new Expression('? = ?', array(new Column('a'), 'b'))),
		);
		$result[] = array(
			array('a' => 'b', 'c' => 'd'),
			array(
				new Expression('? = ?', array(new Column('a'), 'b')),
				new Expression('? = ?', array(new Column('c'), 'd')),
			),
		);

		// data set #3
		$value = new Column('b');
		$result[] = array(
			array('a' => $value),
			array(new Expression('? = ?', array(new Column('a'), $value)))
		);

		$value1 = new Column('b');
		$value2 = new Expression('d');
		$result[] = array(
			array('a' => $value1, 'c' => $value2),
			array(
				new Expression('? = ?', array(new Column('a'), $value1)),
				new Expression('? = ?', array(new Column('c'), $value2)),
			),
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Update::set
	 *
	 * @dataProvider    provider_set
	 *
	 * @param   array   $argument   Argument
	 * @param   array   $values     Expected
	 */
	public function test_set($argument, $values)
	{
		$update = new Update;

		$this->assertSame($update, $update->set($argument));
		$this->assertEquals($values, $update->values);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$update->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Update::set
	 *
	 * @dataProvider    provider_set
	 *
	 * @param   array   $argument   Argument
	 */
	public function test_set_reset($argument)
	{
		$update = new Update;
		$update->set($argument);

		$this->assertSame($update, $update->set(NULL));
		$this->assertNull($update->values);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertSame($this->parameters, $update->parameters);
	}

	public function provider_table()
	{
		return array(
			array(array('a'), new Table('a')),
			array(array(new Expression('a')), new Expression('a')),
			array(array(new Identifier('a')), new Identifier('a')),

			array(array('a', 'b'), new Alias(new Table('a'), 'b')),
			array(
				array('a', new Expression('b')),
				new Alias(new Table('a'), new Expression('b')),
			),
			array(
				array('a', new Identifier('b')),
				new Alias(new Table('a'), new Identifier('b')),
			),
		);
	}

	/**
	 * @covers  SQL\DML\Update::table
	 *
	 * @dataProvider    provider_table
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Table|Alias $table       Expected
	 */
	public function test_table($arguments, $table)
	{
		$update = new Update;

		$this->assertSame(
			$update, call_user_func_array(array($update, 'table'), $arguments)
		);
		$this->assertEquals($table, $update->table);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertEquals(
			array_merge($this->parameters, array(':table' => $table)),
			$update->parameters
		);
	}

	public function provider_value()
	{
		$result[] = array(
			'a', 'b',
			array(new Expression('? = ?', array(new Column('a'), 'b'))),
		);
		$result[] = array(
			array('a'), 'b',
			array(new Expression('? = ?', array(new Column('a'), 'b'))),
		);

		$column = new Expression('a');
		$result[] = array(
			$column, 'b',
			array(new Expression('? = ?', array($column, 'b'))),
		);

		$column = new Identifier('a');
		$result[] = array(
			$column, 'b',
			array(new Expression('? = ?', array($column, 'b'))),
		);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Update::value
	 *
	 * @dataProvider    provider_value
	 *
	 * @param   mixed   $column     First argument
	 * @param   mixed   $value      Second argument
	 * @param   array   $values     Expected
	 */
	public function test_value($column, $value, $values)
	{
		$update = new Update;

		$this->assertSame($update, $update->value($column, $value));
		$this->assertEquals($values, $update->values);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$update->parameters
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
	 * @covers  SQL\DML\Update::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Conditions  $where      Expected
	 */
	public function test_where($arguments, $where)
	{
		$update = new Update;

		$this->assertSame(
			$update, call_user_func_array(array($update, 'where'), $arguments)
		);
		$this->assertEquals($where, $update->where);

		$this->assertSame(
			'UPDATE :table SET :values WHERE :where', (string) $update
		);
		$this->assertEquals(
			array_merge($this->parameters, array(':where' => $where)),
			$update->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Update::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   array       $arguments  Arguments
	 */
	public function test_where_reset($arguments)
	{
		$update = new Update;
		call_user_func_array(array($update, 'where'), $arguments);

		$this->assertSame($update, $update->where(NULL));
		$this->assertNull($update->where);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertSame($this->parameters, $update->parameters);
	}

	/**
	 * @covers  SQL\DML\Update::__toString
	 */
	public function test_toString()
	{
		$update = new Update;
		$update
			->from('a')
			->where('b', '=', 1)
			->limit(2);

		$this->assertSame(
			'UPDATE :table SET :values FROM :from WHERE :where LIMIT :limit',
			(string) $update
		);
	}
}
