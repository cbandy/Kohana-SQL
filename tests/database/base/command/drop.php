<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.ddl
 */
class Database_Base_Command_Drop_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers  Database_Command_Drop::__construct
	 */
	public function test_constructor()
	{
		$db = $this->sharedFixture;

		$this->assertSame('DROP A "b"', $db->quote(new Database_Command_Drop('a', 'b')));
		$this->assertSame('DROP A "b" CASCADE', $db->quote(new Database_Command_Drop('a', 'b', TRUE)));
		$this->assertSame('DROP A "b" RESTRICT', $db->quote(new Database_Command_Drop('a', 'b', FALSE)));
	}

	/**
	 * @covers  Database_Command_Drop::cascade
	 */
	public function test_cascade()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Drop('a', 'b');

		$this->assertSame($command, $command->cascade(), 'Chainable (void)');
		$this->assertSame('DROP A "b" CASCADE', $db->quote($command));

		$this->assertSame($command, $command->cascade(FALSE), 'Chainable (FALSE)');
		$this->assertSame('DROP A "b" RESTRICT', $db->quote($command));

		$this->assertSame($command, $command->cascade(TRUE), 'Chainable (TRUE)');
		$this->assertSame('DROP A "b" CASCADE', $db->quote($command));

		$this->assertSame($command, $command->cascade(NULL), 'Chainable (NULL)');
		$this->assertSame('DROP A "b"', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Drop::if_exists
	 */
	public function test_if_exists()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Drop('a', 'b');

		$this->assertSame($command, $command->if_exists(), 'Chainable (void)');
		$this->assertSame('DROP A IF EXISTS "b"', $db->quote($command));

		$this->assertSame($command, $command->if_exists(FALSE), 'Chainable (FALSE)');
		$this->assertSame('DROP A "b"', $db->quote($command));

		$this->assertSame($command, $command->if_exists(TRUE), 'Chainable (TRUE)');
		$this->assertSame('DROP A IF EXISTS "b"', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Drop::name
	 */
	public function test_name()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Drop('a', 'b');

		$this->assertSame($command, $command->name('c'));
		$this->assertSame('DROP A "c"', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Drop::names
	 */
	public function test_names()
	{
		$db = $this->sharedFixture;
		$command = new Database_Command_Drop('a');

		$this->assertSame($command, $command->names(array('b', 'c')));
		$this->assertSame('DROP A "b", "c"', $db->quote($command));
	}

	/**
	 * @covers  Database_Command_Drop::__toString
	 */
	public function test_toString()
	{
		$command = new Database_Command_Drop('a');
		$command
			->if_exists()
			->name('b')
			->cascade();

		$this->assertSame('DROP A IF EXISTS :name CASCADE', (string) $command);

		$command->cascade(FALSE);
		$this->assertSame('DROP A IF EXISTS :name RESTRICT', (string) $command);
	}
}
