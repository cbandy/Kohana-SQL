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
class DeleteTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':table' => NULL,
		':limit' => NULL,
		':using' => NULL,
		':where' => NULL,
	);

	/**
	 * @covers  SQL\DML\Delete::__construct
	 */
	public function test_constructor_default()
	{
		$delete = new Delete;

		$this->assertSame('DELETE FROM :table', (string) $delete);
		$this->assertSame($this->parameters, $delete->parameters);
	}

	/**
	 * @covers  SQL\DML\Delete::__construct
	 *
	 * @dataProvider    provider_from
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Table|Alias $from       Expected
	 */
	public function test_constructor($arguments, $from)
	{
		$class = new \ReflectionClass('SQL\DML\Delete');
		$delete = $class->newInstanceArgs($arguments);

		$this->assertEquals($from, $delete->from);

		$this->assertSame('DELETE FROM :table', (string) $delete);
		$this->assertEquals(
			array_merge($this->parameters, array(':table' => $from)),
			$delete->parameters
		);
	}

	public function provider_from()
	{
		return array(
			array(array('a'), new Table('a')),
			array(array('a', 'b'), new Alias(new Table('a'), 'b')),
		);
	}

	/**
	 * @covers  SQL\DML\Delete::from
	 *
	 * @dataProvider    provider_from
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Table|Alias $from       Expected
	 */
	public function test_from($arguments, $from)
	{
		$delete = new Delete;

		$this->assertSame(
			$delete, call_user_func_array(array($delete, 'from'), $arguments)
		);
		$this->assertEquals($from, $delete->from);

		$this->assertSame('DELETE FROM :table', (string) $delete);
		$this->assertEquals(
			array_merge($this->parameters, array(':table' => $from)),
			$delete->parameters
		);
	}

	public function provider_limit()
	{
		return array(
			array(NULL, 'DELETE FROM :table'),

			array(0, 'DELETE FROM :table LIMIT :limit'),
			array(1, 'DELETE FROM :table LIMIT :limit'),
		);
	}

	/**
	 * @covers  SQL\DML\Delete::limit
	 *
	 * @dataProvider    provider_limit
	 *
	 * @param   integer $argument   Argument
	 * @param   string  $value
	 */
	public function test_limit($argument, $value)
	{
		$delete = new Delete;

		$this->assertSame($delete, $delete->limit($argument));
		$this->assertSame($argument, $delete->limit);

		$this->assertSame($value, (string) $delete);
		$this->assertSame(
			array_merge($this->parameters, array(':limit' => $argument)),
			$delete->parameters
		);
	}

	public function provider_using()
	{
		$result[] = array(array('a'), new Table_Reference('a'));
		$result[] = array(array('a', 'b'), new Table_Reference('a', 'b'));

		$reference = new Table_Reference('a');
		$result[] = array(array($reference), $reference);

		return $result;
	}

	/**
	 * @covers  SQL\DML\Delete::using
	 *
	 * @dataProvider    provider_using
	 *
	 * @param   array           $arguments  Arguments
	 * @param   Table_Reference $using      Expected
	 */
	public function test_using($arguments, $using)
	{
		$delete = new Delete;

		$this->assertSame(
			$delete, call_user_func_array(array($delete, 'using'), $arguments)
		);
		$this->assertEquals($using, $delete->using);

		$this->assertSame('DELETE FROM :table USING :using', (string) $delete);
		$this->assertEquals(
			array_merge($this->parameters, array(':using' => $using)),
			$delete->parameters
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
	 * @covers  SQL\DML\Delete::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   array       $arguments  Arguments
	 * @param   Conditions  $where      Expected
	 */
	public function test_where($arguments, $where)
	{
		$delete = new Delete;

		$this->assertSame(
			$delete, call_user_func_array(array($delete, 'where'), $arguments)
		);
		$this->assertEquals($where, $delete->where);

		$this->assertSame('DELETE FROM :table WHERE :where', (string) $delete);
		$this->assertEquals(
			array_merge($this->parameters, array(':where' => $where)),
			$delete->parameters
		);
	}

	/**
	 * @covers  SQL\DML\Delete::where
	 *
	 * @dataProvider    provider_where
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_where_reset($arguments)
	{
		$delete = new Delete;
		call_user_func_array(array($delete, 'where'), $arguments);

		$this->assertSame($delete, $delete->where(NULL));
		$this->assertNull($delete->where);

		$this->assertSame('DELETE FROM :table', (string) $delete);
		$this->assertSame($this->parameters, $delete->parameters);
	}

	/**
	 * @covers  SQL\DML\Delete::__toString
	 */
	public function test_toString()
	{
		$delete = new Delete;
		$delete
			->using('a')
			->where('b', '=', 'c')
			->limit(1);

		$this->assertSame(
			'DELETE FROM :table USING :using WHERE :where LIMIT :limit',
			(string) $delete
		);
	}
}
