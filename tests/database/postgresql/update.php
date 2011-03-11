<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Update_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	/**
	 * @covers  Database_PostgreSQL_Update::as_assoc
	 */
	public function test_as_assoc()
	{
		$command = new Database_PostgreSQL_Update;

		$this->assertSame($command, $command->as_assoc(), 'Chainable');
		$this->assertSame(FALSE, $command->as_object);
	}

	public function provider_as_object()
	{
		return array
		(
			array(FALSE),
			array(TRUE),
			array('a'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Update::as_object
	 * @dataProvider    provider_as_object
	 *
	 * @param   string|boolean  $as_object  Expected value
	 */
	public function test_as_object($as_object)
	{
		$command = new Database_PostgreSQL_Update;

		$this->assertSame($command, $command->as_object($as_object), 'Chainable');
		$this->assertSame($as_object, $command->as_object);
	}

	public function provider_from_limit()
	{
		return array
		(
			array(0, 'a'),
			array(0, array('a')),

			array(1, 'a'),
			array(1, array('a')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Update::from
	 * @dataProvider    provider_from_limit
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $from
	 */
	public function test_from_limit($limit, $from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->limit($limit);

		$this->setExpectedException('Kohana_Exception');

		$command->from($from);
	}

	public function provider_from_limit_reset()
	{
		return array
		(
			array(NULL, NULL),

			array(0, NULL),
			array(0, ''),
			array(0, array()),

			array(1, NULL),
			array(1, ''),
			array(1, array()),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Update::from
	 * @dataProvider    provider_from_limit_reset
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $from
	 */
	public function test_from_limit_reset($limit, $from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->limit($limit);

		$this->assertSame($command, $command->from($from));
	}

	public function provider_limit()
	{
		return array
		(
			array(NULL, 'UPDATE "" SET '),
			array(0, 'UPDATE "" SET  WHERE ctid IN (SELECT ctid FROM "" LIMIT 0)'),
			array(1, 'UPDATE "" SET  WHERE ctid IN (SELECT ctid FROM "" LIMIT 1)'),
			array(5, 'UPDATE "" SET  WHERE ctid IN (SELECT ctid FROM "" LIMIT 5)'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_limit
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_limit($value, $expected)
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Update;

		$this->assertSame($command, $command->limit($value), 'Chainable');
		$this->assertSame($expected, $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_from_limit
	 *
	 * @param   mixed   $limit
	 * @param   mixed   $from
	 */
	public function test_limit_from($limit, $from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->from($from);

		$this->setExpectedException('Kohana_Exception');

		$command->limit($limit);
	}

	public function provider_limit_from_reset()
	{
		return array
		(
			array(NULL),

			array(''),
			array('a'),

			array(array()),
			array(array('a')),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_limit_from_reset
	 *
	 * @param   mixed   $from
	 */
	public function test_limit_from_reset($from)
	{
		$command = new Database_PostgreSQL_Update;
		$command->from($from);

		$this->assertSame($command, $command->limit(NULL));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::limit
	 * @dataProvider    provider_limit
	 *
	 * @param   mixed   $value
	 */
	public function test_limit_reset($value)
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Update;
		$command->limit($value);

		$command->limit(NULL);

		$this->assertSame('UPDATE "" SET ', $db->quote($command));
	}

	public function provider_returning()
	{
		return array
		(
			array(NULL, 'UPDATE "" SET '),

			array(
				array('a'),
				'UPDATE "" SET  RETURNING "a"',
			),
			array(
				array('a', 'b'),
				'UPDATE "" SET  RETURNING "a", "b"',
			),
			array(
				array('a' => 'b'),
				'UPDATE "" SET  RETURNING "b" AS "a"',
			),
			array(
				array('a' => 'b', 'c' => 'd'),
				'UPDATE "" SET  RETURNING "b" AS "a", "d" AS "c"',
			),

			array(
				array(new SQL_Column('a')),
				'UPDATE "" SET  RETURNING "a"',
			),
			array(
				array(new SQL_Column('a'), new SQL_Column('b')),
				'UPDATE "" SET  RETURNING "a", "b"',
			),
			array(
				array('a' => new SQL_Column('b')),
				'UPDATE "" SET  RETURNING "b" AS "a"',
			),
			array(
				array('a' => new SQL_Column('b'), 'c' => new SQL_Column('d')),
				'UPDATE "" SET  RETURNING "b" AS "a", "d" AS "c"',
			),

			array(new SQL_Expression('expr'), 'UPDATE "" SET  RETURNING expr'),
		);
	}

	/**
	 * @covers  Database_PostgreSQL_Update::returning
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value
	 * @param   string  $expected
	 */
	public function test_returning($value, $expected)
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Update;

		$this->assertSame($command, $command->returning($value), 'Chainable');
		$this->assertSame($expected, $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::returning
	 * @dataProvider    provider_returning
	 *
	 * @param   mixed   $value
	 */
	public function test_returning_reset($value)
	{
		$db = Database::factory();
		$command = new Database_PostgreSQL_Update;
		$command->returning($value);

		$command->returning(NULL);

		$this->assertSame('UPDATE "" SET ', $db->quote($command));
	}

	/**
	 * @covers  Database_PostgreSQL_Update::__toString
	 */
	public function test_toString()
	{
		$command = new Database_PostgreSQL_Update;

		$this->assertSame('UPDATE :table SET :values', (string) $command);

		$command
			->where(new SQL_Conditions)
			->limit(1)
			->returning('a');

		$this->assertSame('UPDATE :table SET :values WHERE ctid IN (SELECT ctid FROM :table WHERE :where LIMIT :limit) RETURNING :returning', (string) $command);
	}
}
