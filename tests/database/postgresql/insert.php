<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.postgresql
 */
class Database_PostgreSQL_Insert_Test extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		if ( ! extension_loaded('pgsql'))
			throw new PHPUnit_Framework_SkippedTestSuiteError('PostgreSQL extension not installed');

		if ( ! Database::factory() instanceof Database_PostgreSQL)
			throw new PHPUnit_Framework_SkippedTestSuiteError('Database not configured for PostgreSQL');
	}

	protected $_table = 'temp_test_table';

	public function setUp()
	{
		$db = $this->sharedFixture = Database::factory();
		$table = $db->quote_table($this->_table);

		$db->execute_command(implode('; ', array(
			'CREATE TEMPORARY TABLE '.$table.' ("id" bigserial PRIMARY KEY, "value" integer)',
			'INSERT INTO '.$table.' ("value") VALUES (50)',
			'INSERT INTO '.$table.' ("value") VALUES (55)',
			'INSERT INTO '.$table.' ("value") VALUES (60)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
			'INSERT INTO '.$table.' ("value") VALUES (65)',
		)));
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::execute
	 * @covers  Database_PostgreSQL_Insert::identity
	 */
	public function test_identity()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$query = $db->insert($this->_table, array('value'))
			->values(array(75), array(80));

		$this->assertSame($query, $query->identity('id'), 'Chainable (column)');
		$this->assertEquals(array(2,6), $query->execute($db), 'Identity of the _first_ row');

		$this->assertSame($query, $query->identity(new SQL_Expression("'asdf'")), 'Chainable (expression)');

		$this->assertEquals(array(2,'asdf'), $query->execute($db), 'Expression result');

		$this->assertSame($query, $query->identity(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($db), 'No identity');
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::identity
	 */
	public function test_identity_assigned()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$query = $db->insert($this->_table, array('id', 'value'))
			->identity('id');

		$query->values(array(20, 75), array(21, 80));
		$this->assertEquals(array(2,20), $query->execute($db), 'Identity of the first row (literal)');
		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t65\n", "5\t65\n", "20\t75\n", "21\t80\n"), $db->copy_to($this->_table));

		$query->values(NULL)->values(array(new SQL_Expression('DEFAULT'), 85), array(30, 90));
		$this->assertEquals(array(2,6), $query->execute($db), 'Identity of the first row (default)');
		$this->assertEquals(array("1\t50\n", "2\t55\n", "3\t60\n", "4\t65\n", "5\t65\n", "20\t75\n", "21\t80\n", "6\t85\n", "30\t90\n"), $db->copy_to($this->_table));
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::identity
	 */
	public function test_identity_query()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$query = $db->insert($this->_table, array('value'))
			->values($db->query('SELECT 75 as "value" UNION SELECT 80'))
			->identity('id');

		$this->assertEquals(array(2,6), $query->execute($db), 'Identity of the _first_ row');
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::identity
	 */
	public function test_identity_table_expression()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$result = $db->insert(new SQL_Expression($db->quote_table($this->_table)))
			->columns(array('id', 'value'))
			->values(array(20, 75), array(21, 80))
			->identity('id')
			->execute($db);

		$this->assertEquals(array(2,20), $result, 'Identity of the first row');
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::identity
	 */
	public function test_identity_without_columns()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$result = $db->insert($this->_table)
			->values(array(20, 75), array(21, 80))
			->identity('id')
			->execute($db);

		$this->assertEquals(array(2,20), $result, 'Identity of the first row');
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::returning
	 */
	public function test_returning()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$query = $db->insert($this->_table, array('value'))
			->values(array(75), array(80));

		$this->assertSame($query, $query->returning(array('more' => 'id')), 'Chainable (column)');

		$result = $query->execute($db);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals(array(array('more' => 6), array('more' => 7)), $result->as_array(), 'Each aliased column');

		$this->assertSame($query, $query->returning(new SQL_Expression('\'asdf\' AS "rawr"')), 'Chainable (expression)');

		$result = $query->execute($db);

		$this->assertEquals(array(array('rawr' => 'asdf'), array('rawr' => 'asdf')), $result->as_array(), 'Each expression');

		$this->assertSame($query, $query->returning(NULL), 'Chainable (reset)');
		$this->assertSame(2, $query->execute($db));
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::as_assoc
	 * @covers  Database_PostgreSQL_Insert::execute
	 */
	public function test_as_assoc()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$query = $db->insert($this->_table, array('value'))
			->values(array(75), array(80))
			->returning(array('id'));

		$this->assertSame($query, $query->as_assoc(), 'Chainable');

		$result = $query->execute($db);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals(array(array('id' => 6), array('id' => 7)), $result->as_array(), 'Each column');
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::as_object
	 * @covers  Database_PostgreSQL_Insert::execute
	 */
	public function test_as_object()
	{
		$db = $this->sharedFixture;

		if ($db->version() < '8.2')
			$this->markTestSkipped('Not supported');

		$query = $db->insert($this->_table, array('value'))
			->values(array(75), array(80))
			->returning(array('id'));

		$this->assertSame($query, $query->as_object(), 'Chainable');

		$result = $query->execute($db);

		$this->assertType('Database_PostgreSQL_Result', $result);
		$this->assertEquals(array( (object) array('id' => 6), (object) array('id' => 7)), $result->as_array(), 'Each column');
	}

	/**
	 * @covers  Database_PostgreSQL_Insert::__toString
	 */
	public function test_toString()
	{
		$command = new Database_PostgreSQL_Insert;
		$command
			->returning(array('a'));

		$this->assertSame('INSERT INTO :table DEFAULT VALUES RETURNING :returning', (string) $command);
	}
}
