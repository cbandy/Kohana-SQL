<?php
namespace SQL\MySQL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Connection_OptionsTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if ( ! extension_loaded('mysqli'))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('MySQLi extension not installed');

		if (empty($_SERVER['MYSQL_NATIVE']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for MySQL');
	}

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $character_sets;

	/**
	 * @var string
	 */
	protected $default_character_set;

	public function setup()
	{
		$this->config = json_decode($_SERVER['MYSQL_NATIVE'], TRUE);

		$connection = new Connection($this->config);

		$this->character_sets = $connection->execute_query('SHOW CHARACTER SET')
			->to_array(NULL, 'Charset');

		if (count($this->character_sets) <= 1)
			return $this->markTestSkipped('Not enough character sets');

		$this->default_character_set = $this->character_set($connection);
	}

	/**
	 * @return  string  Character set
	 */
	protected function character_set($connection)
	{
		return $connection
			->execute_query("SHOW VARIABLES LIKE 'character_set_client'")
			->get('Value');
	}

	/**
	 * @return  string  Character set
	 */
	protected function other_character_set()
	{
		foreach ($this->character_sets as $character_set)
		{
			if ($character_set != $this->default_character_set)
				return $character_set;
		}
	}

	/**
	 * @covers  SQL\MySQL\Connection::connect
	 */
	public function test_connect_uses_options()
	{
		$config = $this->config;
		$other_character_set = $this->other_character_set();

		$config['options'][MYSQLI_SET_CHARSET_NAME] = $other_character_set;

		$connection = new Connection($config);
		$connection->connect();

		$this->assertSame(
			$other_character_set, $this->character_set($connection)
		);
	}
}
