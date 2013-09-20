<?php
namespace SQL\MySQL;

use SQL\PDO\Connection;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 *
 * @link http://dev.mysql.com/doc/en/time-zone-support.html
 *
 * DATE, 1000-01-01 to 9999-12-31
 * DATETIME, 1000-01-01 00:00:00 to 9999-12-31 23:59:59
 * TIME, -838:59:59 to 838:59:59
 * TIMESTAMP, 1970-01-01 00:00:01 to 2038-01-19 03:14:07, stored in UTC
 *
 * Type keyword (DATE, DATETIME, TIME, TIMESTAMP) are ignored.
 * Fractional seconds cannot be stored.
 * CONVERT_TZ() only supports values that fall within TIMESTAMP.
 */
class TimeZoneTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if (empty($_SERVER['MYSQL']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for MySQL');
	}

	/**
	 * @var Connection
	 */
	protected $connection;

	protected $table = 'kohana_temporal_test';

	/**
	 * @return  boolean Whether or not time zone information has been loaded
	 */
	protected function has_olson_time_zones()
	{
		return (bool) $this->connection->execute_query(
			"SELECT CONVERT_TZ('1990-01-01 00:00:00', 'GMT', 'America/Phoenix')"
		)->get();
	}

	public function setup()
	{
		$config = json_decode($_SERVER['MYSQL'], TRUE);

		$this->connection = new Connection($config);
		$this->connection->execute_command(
			'CREATE TEMPORARY TABLE '.$this->table
			.' (d DATE, dt DATETIME, t TIME, ts TIMESTAMP)'
		);
	}

	public function teardown()
	{
		$this->connection->disconnect();
	}

	/**
	 * @coversNothing
	 */
	public function test_session_time_zone_allows_olson_when_loaded()
	{
		if ( ! $this->has_olson_time_zones())
		{
			$this->setExpectedException(
				'SQL\RuntimeException', 'Unknown or incorrect time zone', 'HY000'
			);
		}

		$this->connection->execute_query("SET SESSION time_zone = 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (ts) VALUES ('1990-01-01 00:00:00')"
		);
		$this->connection->execute_query("SET SESSION time_zone = '+00:00'");

		$this->assertSame(
			'1990-01-01 07:00:00',
			$this->connection->execute_query("SELECT ts FROM $this->table")->get()
		);
	}

	/**
	 * The SQL standard specifies that '+04:00' is west of Greenwich, i.e.
	 * closer to Phoenix than GMT.
	 *
	 * @coversNothing
	 */
	public function test_session_time_zone_does_not_follow_sql_standard()
	{
		if ( ! $this->has_olson_time_zones())
			return $this->markTestIncomplete();

		$this->connection->execute_query("SET SESSION time_zone = 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (ts) VALUES ('1990-01-01 00:00:00')"
		);
		$this->connection->execute_query("SET SESSION time_zone = '+04:00'");

		$local_time = $this->connection->execute_query("SELECT ts FROM $this->table")->get();

		$this->assertNotEquals('1990-01-01 03:00:00', $local_time);
		$this->assertSame('1990-01-01 11:00:00', $local_time);
	}

	/**
	 * @coversNothing
	 */
	public function test_datetime_ignores_literal_time_zone()
	{
		if ( ! $this->has_olson_time_zones())
			return $this->markTestIncomplete();

		$this->connection->execute_command("SET SESSION time_zone = 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (dt) VALUES ('1990-01-01 00:00:00-04:00')"
		);

		$this->assertSame(
			'1990-01-01 00:00:00',
			$this->connection->execute_query("SELECT dt FROM $this->table")->get()
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_datetime_ignores_session_time_zone()
	{
		if ( ! $this->has_olson_time_zones())
			return $this->markTestIncomplete();

		$this->connection->execute_command("SET SESSION time_zone = 'America/Phoenix'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (dt) VALUES ('1990-01-01 00:00:00-04:00')"
		);
		$this->connection->execute_command("SET SESSION time_zone = '+03:00'");

		$this->assertSame(
			'1990-01-01 00:00:00',
			$this->connection->execute_query("SELECT dt FROM $this->table")->get()
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_timestamp_ignores_literal_time_zone()
	{
		if ( ! $this->has_olson_time_zones())
			return $this->markTestIncomplete();

		$this->connection->execute_command("SET SESSION time_zone = 'America/Phoenix'");
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
	public function test_timestamp_uses_session_time_zone()
	{
		$this->connection->execute_command("SET SESSION time_zone = '+02:00'");
		$this->connection->execute_command(
			"INSERT INTO $this->table (ts) VALUES ('1990-01-01 00:00:00-04:00')"
		);
		$this->connection->execute_command("SET SESSION time_zone = '+05:00'");

		$this->assertSame(
			'1990-01-01 03:00:00',
			$this->connection->execute_query("SELECT ts FROM $this->table")->get()
		);
	}
}
