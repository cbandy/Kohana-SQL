<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.query
 * @group   database.query.cached
 */
class Database_Query_Cached_Test extends PHPUnit_Framework_TestCase
{
	protected $_db;
	protected $_table;

	public function setUp()
	{
		$this->_db = Database::instance('testing');
		$this->_table = $this->_db->quote_table('temp_test_table');

		$this->_db->execute_command('CREATE TEMPORARY TABLE '.$this->_table.' (value integer)');
		$this->_db->execute_command('INSERT INTO '.$this->_table.' (value) VALUES (50)');
	}

	public function tearDown()
	{
		$this->_db->disconnect();
	}

	public function test_delete()
	{
		$query = new Database_Query('SELECT * FROM '.$this->_table);
		$class = get_class($query->execute($this->_db));

		$cached = new Database_Query_Cached(5, $this->_db, $query);

		// Cache the result
		$cached->execute();

		// Clear the cache
		$cached->delete();
		$this->assertType($class, $cached->execute(), 'Not cached');
	}

	public function test_execute()
	{
		$query = new Database_Query('SELECT * FROM '.$this->_table);
		$class = get_class($query->execute($this->_db));

		$cached = new Database_Query_Cached(5, $this->_db, $query);

		// Clear the cache
		$cached->delete();

		$this->assertType($class, $cached->execute(), 'First execution not cached');
		$this->assertType('Database_Result_Array', $cached->execute(), 'Second execution cached');
	}

	public function test_as_assoc()
	{
		$cached = new Database_Query_Cached(5, $this->_db, new Database_Query('SELECT * FROM '.$this->_table));

		// Clear the cache
		$cached->delete();

		$this->assertSame($cached, $cached->as_assoc(), 'Chainable');
		$this->assertType('array', $cached->execute()->current(), 'Array result');
		$this->assertType('array', $cached->execute()->current(), 'Array result, cached');
	}

	public function test_as_object()
	{
		$cached = new Database_Query_Cached(5, $this->_db, new Database_Query('SELECT * FROM '.$this->_table));

		// Clear the cache
		$cached->delete();

		$this->assertSame($cached, $cached->as_object(), 'Chainable (void)');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result, cached');

		$this->assertSame($cached, $cached->as_object(), 'Chainable (TRUE)');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result');
		$this->assertType('stdClass', $cached->execute()->current(), 'Object result, cached');

		$this->assertSame($cached, $cached->as_object(FALSE), 'Chainable (FALSE)');
		$this->assertType('array', $cached->execute()->current(), 'Array result');
		$this->assertType('array', $cached->execute()->current(), 'Array result, cached');

		$this->assertSame($cached, $cached->as_object('Database_Query_Cached_Test_Class'), 'Chainable (Database_Query_Cached_Test_Class)');
		$this->assertType('Database_Query_Cached_Test_Class', $cached->execute()->current(), 'Class result');
		$this->assertType('Database_Query_Cached_Test_Class', $cached->execute()->current(), 'Class result, cached');
	}

	public function test_prepared()
	{
		$query = $this->_db->prepare_query('SELECT * FROM '.$this->_table);
		$class = get_class($query->execute());

		$cached = new Database_Query_Cached(5, $this->_db, $query);

		// Clear the cache
		$cached->delete();

		$this->assertType($class, $cached->execute(), 'First execution not cached');
		$this->assertType('Database_Result_Array', $cached->execute(), 'Second execution cached');
	}
}

class Database_Query_Cached_Test_Class {}
