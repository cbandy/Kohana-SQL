<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_SQL_DDL_Constraint_Primary_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL_DDL_Constraint_Primary::__construct
	 */
	public function test_constructor()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));

		$this->assertSame('PRIMARY KEY', $db->quote(new SQL_DDL_Constraint_Primary));
		$this->assertSame('PRIMARY KEY', $db->quote(new SQL_DDL_Constraint_Primary(array())));
		$this->assertSame('PRIMARY KEY ("a")', $db->quote(new SQL_DDL_Constraint_Primary(array('a'))));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Primary::columns
	 */
	public function test_columns()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$constraint = new SQL_DDL_Constraint_Primary;

		$this->assertSame($constraint, $constraint->columns(array('a')), 'Chainable');
		$this->assertSame('PRIMARY KEY ("a")', $db->quote($constraint));
	}

	/**
	 * @covers  SQL_DDL_Constraint_Primary::__toString
	 */
	public function test_toString()
	{
		$constraint = new SQL_DDL_Constraint_Primary;
		$constraint
			->name('a')
			->columns(array('b'));

		$this->assertSame('CONSTRAINT :name PRIMARY KEY (:columns)', (string) $constraint);
	}
}
