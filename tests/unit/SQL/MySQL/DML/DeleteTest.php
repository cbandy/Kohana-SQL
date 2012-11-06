<?php
namespace SQL\MySQL\DML;

use SQL\Column;
use SQL\Expression;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
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
		':orderby' => NULL,
	);

	public function provider_order_by()
	{
		return array(
			array(array(NULL), NULL, 'DELETE FROM :table'),
			array(array(NULL, 'any'), NULL, 'DELETE FROM :table'),
			array(array(NULL, new Expression('any')), NULL, 'DELETE FROM :table'),

			array(
				array('a'), array(new Column('a')),
				'DELETE FROM :table ORDER BY :orderby',
			),
			array(
				array('a', 'b'),
				array(new Expression('? B', array(new Column('a')))),
				'DELETE FROM :table ORDER BY :orderby',
			),
			array(
				array('a', new Expression('b')),
				array(new Expression('? ?', array(
					new Column('a'), new Expression('b'))
				)),
				'DELETE FROM :table ORDER BY :orderby',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'DELETE FROM :table ORDER BY :orderby',
			),
			array(
				array(new Column('a'), 'b'),
				array(new Expression('? B', array(new Column('a')))),
				'DELETE FROM :table ORDER BY :orderby',
			),
			array(
				array(new Column('a'), new Expression('b')),
				array(new Expression('? ?', array(
					new Column('a'), new Expression('b'))
				)),
				'DELETE FROM :table ORDER BY :orderby',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'DELETE FROM :table ORDER BY :orderby',
			),
			array(
				array(new Expression('a'), 'b'),
				array(new Expression('? B', array(new Expression('a')))),
				'DELETE FROM :table ORDER BY :orderby',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('? ?', array(
					new Expression('a'), new Expression('b'))
				)),
				'DELETE FROM :table ORDER BY :orderby',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DML\Delete::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $order_by   Expected
	 * @param   string  $value
	 */
	public function test_order_by($arguments, $order_by, $value)
	{
		$delete = new Delete;

		$this->assertSame(
			$delete,
			call_user_func_array(array($delete, 'order_by'), $arguments)
		);
		$this->assertEquals($order_by, $delete->order_by);

		$this->assertSame($value, (string) $delete);
		$this->assertEquals(
			array_merge($this->parameters, array(':orderby' => $order_by)),
			$delete->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DML\Delete::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_order_by_reset($arguments)
	{
		$delete = new Delete;

		call_user_func_array(array($delete, 'order_by'), $arguments);

		$this->assertSame($delete, $delete->order_by(NULL));
		$this->assertNull($delete->order_by);

		$this->assertSame('DELETE FROM :table', (string) $delete);
		$this->assertSame($this->parameters, $delete->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DML\Delete::__toString
	 */
	public function test_toString()
	{
		$delete = new Delete;
		$delete
			->from('a')
			->using('b')
			->where('c', '=', 'd')
			->order_by('e', 'f')
			->limit(1);

		$this->assertSame(
			'DELETE FROM :table USING :using WHERE :where ORDER BY :orderby LIMIT :limit',
			(string) $delete
		);
	}
}
