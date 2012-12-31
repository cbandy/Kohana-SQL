<?php
namespace SQL\SQLServer\DML;

/**
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\SQLServer\DML\Select::__toString
	 */
	public function test_toString()
	{
		$select = new Select;
		$select
			->distinct()
			->from('a')
			->where('b', '=', 0)
			->group_by(array('c'))
			->having('d', '=', 1)
			->order_by('e');

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby',
			(string) $select
		);
	}

	/**
	 * @covers  SQL\SQLServer\DML\Select::__toString
	 */
	public function test_toString_limit()
	{
		$select = new Select;
		$select->limit(1);

		$this->assertSame('SELECT TOP (:limit) *', (string) $select);
	}

	/**
	 * @covers  SQL\SQLServer\DML\Select::__toString
	 */
	public function test_toString_offset()
	{
		$select = new Select;
		$select->offset(1);

		$table = 'kohana_5da2f39842640c0bbce187fab674cdca286f09ec';
		$this->assertSame(
			'SELECT * FROM (SELECT *,'
			.' ROW_NUMBER() OVER(ORDER BY :orderby) AS kohana_row_number'
			.') AS '.$table.' WHERE '.$table.'.kohana_row_number > :offset',
			(string) $select
		);
	}

	/**
	 * @covers  SQL\SQLServer\DML\Select::__toString
	 */
	public function test_toString_offset_limit()
	{
		$select = new Select;
		$select
			->limit(1)
			->offset(2);

		$table = 'kohana_2c0ab8df4ea2920719768c60464440b1be5ab87c';
		$this->assertSame(
			'SELECT * FROM (SELECT *,'
			.' ROW_NUMBER() OVER(ORDER BY :orderby) AS kohana_row_number'
			.') AS '.$table.' WHERE '.$table.'.kohana_row_number > :offset'
			.' AND '.$table.'.kohana_row_number <= (:offset + :limit)',
			(string) $select
		);
	}
}
