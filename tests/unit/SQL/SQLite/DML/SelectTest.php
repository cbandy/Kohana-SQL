<?php
namespace SQL\SQLite\DML;

/**
 * @package     SQL
 * @subpackage  SQLite
 * @author      Chris Bandy
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\SQLite\DML\Select::__toString
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
			->limit(1);

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $select
		);

		$select->value('h');

		$this->assertSame(
			'SELECT DISTINCT :values FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $select
		);
	}

	/**
	 * @covers  SQL\SQLite\DML\Select::__toString
	 */
	public function test_toString_with_offset()
	{
		$select = new Select;
		$select->offset(1);

		$this->assertSame(
			'SELECT * LIMIT :offset,9223372036854775807', (string) $select
		);

		$select->limit(1);

		$this->assertSame('SELECT * LIMIT :offset,:limit', (string) $select);
	}
}
