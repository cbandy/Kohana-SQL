<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Constraint_Unique_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DDL_Constraint_Unique::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame('UNIQUE', $db->quote(new SQL_DDL_Constraint_Unique));
		$this->assertSame('UNIQUE', $db->quote(new SQL_DDL_Constraint_Unique(array())));
		$this->assertSame('UNIQUE ("a")', $db->quote(new SQL_DDL_Constraint_Unique(array('a'))));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Unique::columns
	 */
	public function test_columns()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = new SQL_DDL_Constraint_Unique;

		$this->assertSame($constraint, $constraint->columns(array('a')), 'Chainable');
		$this->assertSame('UNIQUE ("a")', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Unique::__toString
	 */
	public function test_toString()
	{
		$constraint = new SQL_DDL_Constraint_Unique;
		$constraint
			->name('a')
			->columns(array('b'));

		$this->assertSame('CONSTRAINT :name UNIQUE (:columns)', (string) $constraint);
	}
}
