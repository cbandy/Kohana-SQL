<?php
namespace SQL\PostgreSQL;

use SQL\Replication_Cluster;

class Cluster extends Replication_Cluster
{
	const INSERT_LOCATION = 'SELECT pg_current_xlog_insert_location()';
	const REPLAY_LAG      = 'SELECT EXTRACT(EPOCH FROM now() - pg_last_xact_replay_timestamp())';
	const REPLAY_LOCATION = 'SELECT pg_last_xlog_replay_location()';

	protected $master;
	protected $slaves;

	public function __construct($master, $slaves)
	{
		$this->master = $master;
		$this->slaves = $slaves;

		if ($this->slaves)
		{
			shuffle($this->slaves);
		}
	}

	public function any()
	{
		$slave = reset($this->slaves);

		return $slave ? $slave : $master;
	}

	public function find_slave($block)
	{
		foreach ($this->slaves as $slave)
		{
			if ($block($slave))
				return $slave;
		}

		return NULL;
	}

	public function master()
	{
		return $this->master;
	}

	public function replication_event()
	{
		return $this->master->execute_query(INSERT_LOCATION)->get();
	}

	public function tolerant_of_event($event)
	{
		$slave = $this->find_slave(function ($slave) use ($event)
		{
			return $event < $slave->execute_query(REPLAY_LOCATION)->get();
		});

		return $slave ? $slave : $this->master;
	}

	public function tolerant_of_time($delay)
	{
		$slave = $this->find_slave(function ($slave) use ($delay)
		{
			return $slave->execute_query(REPLAY_LAG)->get() < $delay;
		});

		return $slave ? $slave : $this->master;
	}
}
