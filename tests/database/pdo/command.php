<?php
/**
 * @package RealDatabase
 * @author  Chris Bandy
 *
 * @group   database
 * @group   database.pdo
 */
class Database_PDO_Command_Test extends PHPUnit_Framework_TestCase
{
	protected $_table = 'temp_test_table';
	protected $_column = 'value';

	public function setUp()
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$db->execute_command('CREATE TEMPORARY TABLE '.$table.' ('.$column.' integer)');
	}

	public function tearDown()
	{
		$db = $this->sharedFixture;

		$db->disconnect();
	}

	/**
	 * @dataProvider    provider_bind
	 */
	public function test_bind($initial, $expected, $changed, $after)
	{
		$db = $this->sharedFixture;
		$table = $db->quote_table($this->_table);
		$column = $db->quote_column($this->_column);

		$command = $db->prepare_command("DELETE FROM $table WHERE $column = ?", array($initial));

		$var = $initial;
		$this->assertSame($command, $command->bind(1, $var), 'Chainable');
		$this->assertSame($expected, $command->parameters[1], 'Parameter visible');
		$this->assertSame($expected, $var, 'Modified by PDO during bind');

		$var = $changed;
		$this->assertSame($changed, $command->parameters[1], 'Changed by reference');

		$command->execute();
		$this->assertSame($after, $var, 'Modified by PDO during execution');
	}

	public function provider_bind()
	{
		return array
		(
			array('a', 'a', 'b', 'b'),
			array(1, 1, 2, '2'),
			array(FALSE, FALSE, TRUE, '1'),
			array(new Database_Binary('a'), 'a', new Database_Binary('b'), 'b'),
		);
	}
}