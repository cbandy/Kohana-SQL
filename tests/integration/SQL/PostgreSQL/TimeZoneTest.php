<?php
namespace SQL\PostgreSQL;

use SQL\PDO\Connection;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class TimeZoneTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if (empty($_SERVER['POSTGRESQL']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for PostgreSQL');
	}

	/**
	 * @var Connection
	 */
	protected $connection;

	protected $table = 'kohana_temporal_test';

	public function setup()
	{
		$config = json_decode($_SERVER['POSTGRESQL'], TRUE);

		$this->connection = new Connection($config);
		$this->connection->execute_command('SET SESSION datestyle TO ISO');
		$this->connection->execute_command(
			'CREATE TEMPORARY TABLE '.$this->table
			.' (d DATE, t TIME, ttz TIME WITH TIME ZONE, ts TIMESTAMP, tstz TIMESTAMP WITH TIME ZONE)'
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_session_time_zone_allows_olson()
	{
		$this->connection->execute_command("SET SESSION TIME ZONE 'America/Phoenix'");
		$offset = $this->connection->execute_query(
			'SELECT EXTRACT(TIMEZONE_HOUR FROM CURRENT_TIMESTAMP)'
		)->get();

		$this->assertSame('-7', $offset);
	}

	/**
	 * @coversNothing
	 */
	public function test_session_time_zone_follows_sql_standard()
	{
		$this->connection->execute_command("SET SESSION TIME ZONE '+04:00'");
		$offset = $this->connection->execute_query(
			'SELECT EXTRACT(TIMEZONE_HOUR FROM CURRENT_TIMESTAMP)'
		)->get();

		$this->assertSame('-4', $offset);
	}

	/**
	 * @coversNothing
	 */
	public function test_timestamp_ignores_session_time_zone()
	{
		$this->connection->execute_command("SET SESSION TIME ZONE 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (ts) VALUES ('1990-01-01 00:00:00')"
		);
		$this->connection->execute_command("SET SESSION TIME ZONE '+02:00'");

		$this->assertSame(
			'1990-01-01 00:00:00',
			$this->connection->execute_query("SELECT ts FROM $this->table")->get()
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_timestamp_ignores_literal_time_zone()
	{
		$this->connection->execute_command("SET SESSION TIME ZONE 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (ts) VALUES ('1990-01-01 00:00:00-04:00')"
		);

		$this->assertSame(
			'1990-01-01 00:00:00',
			$this->connection->execute_query("SELECT ts FROM $this->table")->get()
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_timestamp_with_time_zone_uses_session_time_zone()
	{
		$this->connection->execute_command("SET SESSION TIME ZONE 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (tstz) VALUES ('1990-01-01 00:00:00')"
		);

		$this->assertSame(
			'1990-01-01 00:00:00-07',
			$this->connection->execute_query("SELECT tstz FROM $this->table")->get()
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_timestamp_with_time_zone_uses_literal_time_zone()
	{
		$this->connection->execute_command("SET SESSION TIME ZONE 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (tstz) VALUES ('1990-01-01 00:00:00-10:00')"
		);

		$this->assertSame(
			'1990-01-01 03:00:00-07',
			$this->connection->execute_query("SELECT tstz FROM $this->table")->get()
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_timestamp_with_time_zone_allows_olson()
	{
		$this->connection->execute_command("SET SESSION TIME ZONE 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (tstz) VALUES ('1990-01-01 00:00:00 America/Los_Angeles')"
		);

		$this->assertSame(
			'1990-01-01 01:00:00-07',
			$this->connection->execute_query("SELECT tstz FROM $this->table")->get()
		);
	}
}
