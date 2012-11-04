<?php
namespace SQL\MySQL\DML;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\MySQL\DML\Select::__toString
	 */
	public function test_toString()
	{
		$select = new Select;
		$select
			->distinct()
			->where('a', '=', 'b')
			->group_by(array('c'))
			->having('d', '=', 'e')
			->order_by('f')
			->limit(1);

		$this->assertSame(
			'SELECT DISTINCT * FROM DUAL WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $select
		);

		$select->from('g');

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $select
		);

		$select->column('h');

		$this->assertSame(
			'SELECT DISTINCT :values FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit',
			(string) $select
		);
	}

	/**
	 * @covers  SQL\MySQL\DML\Select::__toString
	 */
	public function test_toString_with_offset()
	{
		$select = new Select;
		$select->offset(1);

		$this->assertSame(
			'SELECT * LIMIT :offset,18446744073709551615', (string) $select
		);

		$select->limit(1);

		$this->assertSame(
			'SELECT * LIMIT :offset,:limit', (string) $select
		);
	}
}
