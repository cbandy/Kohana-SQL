<?php
namespace SQL\SQLServer;

use SQL\PDO\Connection;

/**
 * @package     SQL
 * @subpackage  Microsoft SQL Server
 * @author      Chris Bandy
 *
 * @link http://msdn.microsoft.com/en-us/library/ff848733.aspx
 */
class TimeZoneTest extends \PHPUnit_Framework_TestCase
{
	public static function setupbeforeclass()
	{
		if (empty($_SERVER['SQLSERVER']))
			throw new \PHPUnit_Framework_SkippedTestSuiteError('Not configured for SQL Server');
	}

	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @var string  ISO offset of the server
	 */
	protected $offset;

	protected $table = '#kohana_temporal_table';

	/**
	 * @var string  Version of SQL Server
	 */
	protected $version;

	protected function is_sql_server_2008_or_later()
	{
		return version_compare($this->version, '10.0', '>=');
	}

	/**
	 * @param   integer $offset Hours to add to the session time zone offset
	 * @return  string  ISO offset
	 */
	protected function offset_from_session_offset($offset)
	{
		list($hours, $minutes) = explode(':', $this->offset, 2);

		return sprintf('%+03d:%02d', $hours + $offset, $minutes);
	}

	protected function session_time_zone_offset()
	{
		$minutes = $this->connection
			->execute_query('SELECT DATEDIFF(minute, GETUTCDATE(), GETDATE())')
			->get();

		return sprintf('%+03d:%02d', $minutes / 60, abs($minutes % 60));
	}

	public function setup()
	{
		$config = json_decode($_SERVER['SQLSERVER'], TRUE);

		$this->connection = new Connection($config);
		$this->offset = $this->session_time_zone_offset();
		$this->version = $this->connection
			->execute_query("SELECT SERVERPROPERTY('productversion')")
			->get();

		$this->connection->execute_command(
			'CREATE TABLE '.$this->table.' (dt DATETIME, sdt SMALLDATETIME)'
		);

		if ($this->is_sql_server_2008_or_later())
		{
			$this->connection->execute_command(
				'ALTER TABLE '.$this->table
				.' ADD d DATE, dt2 DATETIME2, dto DATETIMEOFFSET, t TIME'
			);
		}
	}

	/**
	 * @coversNothing
	 */
	public function test_datetime_forbids_literal_time_zone()
	{
		$this->setExpectedException(
			'SQL\RuntimeException', 'datetime format', '22007'
		);

		$this->connection->execute_command(
			"INSERT INTO $this->table (dt) VALUES ('1990-01-01 00:00:00+00:00')"
		);
	}

	/**
	 * @coversNothing
	 */
	public function test_smalldatetime_forbids_literal_time_zone()
	{
		$this->setExpectedException(
			'SQL\RuntimeException', 'datetime format', '22007'
		);

		$this->connection->execute_command(
			"INSERT INTO $this->table (sdt) VALUES ('1990-01-01 00:00:00+00:00')"
		);
	}

	/**
	 * DATETIME2 is available in SQL Server 2008 and later.
	 *
	 * @coversNothing
	 */
	public function test_datetime2_ignores_literal_time_zone()
	{
		if ( ! $this->is_sql_server_2008_or_later())
			return $this->markTestSkipped();

		$this->connection->execute_command(
			"INSERT INTO $this->table (dt2) VALUES"
			." ('1990-01-01 00:00:00{$this->offset_from_session_offset(-4)}')"
		);

		$this->assertSame(
			'1990-01-01 00:00:00.0000000',
			$this->connection->execute_query("SELECT dt2 FROM $this->table")->get()
		);
	}

	/**
	 * DATETIMEOFFSET is available in SQL Server 2008 and later.
	 *
	 * @coversNothing
	 */
	public function test_datetimeoffset_stores_literal_time_zone()
	{
		if ( ! $this->is_sql_server_2008_or_later())
			return $this->markTestSkipped();

		$this->connection->execute_command(
			"INSERT INTO $this->table (dto) VALUES"
			." ('1990-01-01 00:00:00{$this->offset_from_session_offset(-4)}')"
		);

		$this->assertSame(
			'1990-01-01 00:00:00.0000000 '.$this->offset_from_session_offset(-4),
			$this->connection->execute_query("SELECT dto FROM $this->table")->get()
		);
	}

	/**
	 * DATETIMEOFFSET is available in SQL Server 2008 and later.
	 *
	 * @todo Test this on a server outside of GMT.
	 *
	 * @coversNothing
	 */
	public function test_datetimeoffset_literal_without_time_zone_uses_utc()
	{
		if ( ! $this->is_sql_server_2008_or_later())
			return $this->markTestSkipped();

		$this->connection->execute_command(
			"INSERT INTO $this->table (dto) VALUES ('1990-01-01 00:00:00')"
		);

		$this->assertSame(
			'1990-01-01 00:00:00.0000000 +00:00',
			$this->connection->execute_query("SELECT dto FROM $this->table")->get()
		);
	}
}
