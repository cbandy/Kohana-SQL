<?php
namespace SQL\SQLite\DML;

use SQL\Expression;
use SQL\DML\Select;

/**
 * @package     SQL
 * @subpackage  SQLite
 * @author      Chris Bandy
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

	public function provider_add_basic_query_while_empty()
	{
		$result = array(
			array(NULL, new Expression('')),
			array(NULL, new Expression('a')),
			array(NULL, new Select),

			array('a', new Expression('')),
			array('b', new Expression('a')),
			array('c', new Select),
		);

		$select = new Select;
		$select->offset(0);

		$result[] = array(NULL, $select);
		$result[] = array('a', $select);

		return $result;
	}

	/**
	 * @covers  SQL\SQLite\DML\Set::add
	 *
	 * @dataProvider    provider_add_basic_query_while_empty
	 *
	 * @param   string      $operator   First argument to the method
	 * @param   Expression  $query      Second argument to the method
	 */
	public function test_add_basic_query_while_empty($operator, $query)
	{
		$set = new Set;

		$this->assertSame($set, $set->add($operator, $query));
		$this->assertSame('?', (string) $set);
		$this->assertSame(
			array_merge($this->parameters, array($query)),
			$set->parameters
		);
	}

	public function provider_add_certain_selects_while_empty()
	{
		$result = array();

		$select = new Select;
		$select->limit(0);

		$result[] = array(NULL, $select);
		$result[] = array('a', $select);

		$select = new Select;
		$select->limit(5);

		$result[] = array(NULL, $select);
		$result[] = array('b', $select);

		$select = new Select;
		$select->offset(5);

		$result[] = array(NULL, $select);
		$result[] = array('c', $select);

		$select = new Select;
		$select->order_by('a');

		$result[] = array(NULL, $select);
		$result[] = array('d', $select);

		return $result;
	}

	/**
	 * @covers  SQL\SQLite\DML\Set::add
	 *
	 * @dataProvider    provider_add_certain_selects_while_empty
	 *
	 * @param   string      $operator   First argument to the method
	 * @param   Expression  $query      Second argument to the method
	 */
	public function test_add_certain_selects_while_empty($operator, $query)
	{
		$expected = new Select;
		$expected->from($query);

		$set = new Set;

		$this->assertSame($set, $set->add($operator, $query));
		$this->assertSame('?', (string) $set);
		$this->assertEquals(
			array_merge($this->parameters, array($expected)),
			$set->parameters
		);
	}


	public function provider_add_basic_query_while_not_empty()
	{
		$result = array(
			array(NULL, new Expression(''), '?  ?'),
			array(NULL, new Expression('a'), '?  ?'),
			array(NULL, new Select, '?  ?'),

			array('a', new Expression(''), '? A ?'),
			array('b', new Expression('a'), '? B ?'),
			array('c', new Select, '? C ?'),
		);

		$select = new Select;
		$select->offset(0);

		$result[] = array(NULL, $select, '?  ?');
		$result[] = array('a', $select, '? A ?');

		return $result;
	}

	/**
	 * @covers  SQL\SQLite\DML\Set::add
	 *
	 * @dataProvider    provider_add_basic_query_while_not_empty
	 *
	 * @param   string      $operator   First argument to the method
	 * @param   Expression  $query      Second argument to the method
	 * @param   string      $value      Expected
	 */
	public function test_add_basic_query_while_not_empty($operator, $query, $value)
	{
		$previous = new Expression('');
		$set = new Set($previous);

		$this->assertSame($set, $set->add($operator, $query));
		$this->assertSame($value, (string) $set);
		$this->assertSame(
			array_merge($this->parameters, array($previous, $query)),
			$set->parameters
		);
	}

	public function provider_add_certain_selects_while_not_empty()
	{
		$result = array();

		$select = new Select;
		$select->limit(0);

		$result[] = array(NULL, $select, '?  ?');
		$result[] = array('a', $select, '? A ?');

		$select = new Select;
		$select->limit(5);

		$result[] = array(NULL, $select, '?  ?');
		$result[] = array('b', $select, '? B ?');

		$select = new Select;
		$select->offset(5);

		$result[] = array(NULL, $select, '?  ?');
		$result[] = array('c', $select, '? C ?');

		$select = new Select;
		$select->order_by('a');

		$result[] = array(NULL, $select, '?  ?');
		$result[] = array('d', $select, '? D ?');

		return $result;
	}

	/**
	 * @covers  SQL\SQLite\DML\Set::add
	 *
	 * @dataProvider    provider_add_certain_selects_while_not_empty
	 *
	 * @param   string      $operator   First argument to the method
	 * @param   Expression  $query      Second argument to the method
	 * @param   string      $value      Expected
	 */
	public function test_add_certain_selects_while_not_empty($operator, $query, $value)
	{
		$expected = new Select;
		$expected->from($query);

		$previous = new Expression('');
		$set = new Set($previous);

		$this->assertSame($set, $set->add($operator, $query));
		$this->assertSame($value, (string) $set);
		$this->assertEquals(
			array_merge($this->parameters, array($previous, $expected)),
			$set->parameters
		);
	}
}
