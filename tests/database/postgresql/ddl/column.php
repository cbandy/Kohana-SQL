<?php
/**
 * @package     RealDatabase
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_DDL_Column_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new Database_PostgreSQL_DDL_Column('a');

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" SERIAL PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity_bigint()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new Database_PostgreSQL_DDL_Column('a', 'bigint');

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" BIGSERIAL PRIMARY KEY', $db->quote($column));

		// Repeated call should stay the same
		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" BIGSERIAL PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity_prior_constraint()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new Database_PostgreSQL_DDL_Column('a');
		$column->constraint(new SQL_DDL_Constraint_Unique);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" SERIAL UNIQUE PRIMARY KEY', $db->quote($column));
	}

	/**
	 * @covers  Database_PostgreSQL_DDL_Column::identity
	 */
	public function test_identity_prior_primary_key()
	{
		$db = $this->getMockForAbstractClass('Database', array('name', array()));
		$column = new Database_PostgreSQL_DDL_Column('a');
		$column->constraint(new SQL_DDL_Constraint_Primary);

		$this->assertSame($column, $column->identity());
		$this->assertSame('"a" SERIAL PRIMARY KEY', $db->quote($column));
	}
}
