<?php
namespace SQL\MySQL\DML;

use SQL\Column;
use SQL\Expression;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class UpdateTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':from' => NULL,
		':limit' => NULL,
		':values' => NULL,
		':table' => NULL,
		':where' => NULL,
		':orderby' => NULL,
	);

	public function provider_order_by()
	{
		return array(
			array(array(NULL), NULL, 'UPDATE :table SET :values'),
			array(array(NULL, 'any'), NULL, 'UPDATE :table SET :values'),
			array(array(NULL, new Expression('any')), NULL, 'UPDATE :table SET :values'),

			array(
				array('a'), array(new Column('a')),
				'UPDATE :table SET :values ORDER BY :orderby',
			),
			array(
				array('a', 'b'),
				array(new Expression('? B', array(new Column('a')))),
				'UPDATE :table SET :values ORDER BY :orderby',
			),
			array(
				array('a', new Expression('b')),
				array(new Expression('? ?', array(
					new Column('a'), new Expression('b'))
				)),
				'UPDATE :table SET :values ORDER BY :orderby',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'UPDATE :table SET :values ORDER BY :orderby',
			),
			array(
				array(new Column('a'), 'b'),
				array(new Expression('? B', array(new Column('a')))),
				'UPDATE :table SET :values ORDER BY :orderby',
			),
			array(
				array(new Column('a'), new Expression('b')),
				array(new Expression('? ?', array(
					new Column('a'), new Expression('b'))
				)),
				'UPDATE :table SET :values ORDER BY :orderby',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'UPDATE :table SET :values ORDER BY :orderby',
			),
			array(
				array(new Expression('a'), 'b'),
				array(new Expression('? B', array(new Expression('a')))),
				'UPDATE :table SET :values ORDER BY :orderby',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('? ?', array(
					new Expression('a'), new Expression('b'))
				)),
				'UPDATE :table SET :values ORDER BY :orderby',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DML\Update::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $order_by   Expected property
	 * @param   string  $value
	 */
	public function test_order_by($arguments, $order_by, $value)
	{
		$update = new Update;

		$this->assertSame(
			$update,
			call_user_func_array(array($update, 'order_by'), $arguments)
		);
		$this->assertEquals($order_by, $update->order_by);

		$this->assertSame($value, (string) $update);
		$this->assertEquals(
			array_merge($this->parameters, array(':orderby' => $order_by)),
			$update->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DML\Update::order_by
	 *
	 * @dataProvider    provider_order_by
	 *
	 * @param   array   $arguments  Arguments
	 */
	public function test_order_by_reset($arguments)
	{
		$update = new Update;

		call_user_func_array(array($update, 'order_by'), $arguments);

		$this->assertSame($update, $update->order_by(NULL));
		$this->assertNull($update->order_by);

		$this->assertSame('UPDATE :table SET :values', (string) $update);
		$this->assertSame($this->parameters, $update->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DML\Update::__toString
	 */
	public function test_toString()
	{
		$update = new Update;
		$update
			->table('a')
			->set(array('b' => 0))
			->from('c')
			->where('d', '=', 1)
			->order_by('e', 'f')
			->limit(2);

		$this->assertSame(
			'UPDATE :table SET :values WHERE :where ORDER BY :orderby LIMIT :limit',
			(string) $update
		);
	}
}
