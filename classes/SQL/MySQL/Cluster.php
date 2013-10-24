<?php
namespace SQL\MySQL;

use SQL\Replication_Cluster;

class Cluster extends Replication_Cluster
{
	const GTID_EXECUTED = 'SELECT @@gtid_executed';
	const GTID_MODE     = 'SELECT @@gtid_mode';
	const MASTER_STATUS = 'SHOW MASTER STATUS';
	const SLAVE_STATUS  = 'SHOW SLAVE STATUS';

	protected $master;
	protected $slaves;
	protected $use_gtid;

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

		return $slave ? $slave : $this->master;
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
		if ($this->use_gtid())
			return $this->master->execute_query(GTID_EXECUTED)->get();

		$status = $this->master->execute_query(MASTER_STATUS)->current();

		return $status['File'].'|'.$status['Position'];
	}

	public function tolerant_of_event($event)
	{
		if ($this->use_gtid())
		{
			$slave = $this->find_slave(function ($slave) use ($event)
			{
				return $slave->execute_query(
					"SELECT GTID_SUBSET('$event', @@gtid_executed)"
				)->get();
			});
		}
		else
		{
			$slave = $this->find_slave(function ($slave) use ($event)
			{
				$status = $slave->execute_query(SLAVE_STATUS)->current();

				return $event < ($status['Master_Log_File'].'|'
					.$status['Exec_Master_Log_Pos']);
			});
		}

		return $slave ? $slave : $this->master;
	}

	public function tolerant_of_time($delay)
	{
		$slave = $this->find_slave(function ($slave) use ($delay)
		{
			$status = $slave->execute_query(SLAVE_STATUS)->current();

			return ($status['Slave_IO_Running'] === 'Yes'
				AND $status['Slave_SQL_Running'] === 'Yes'
				AND $status['Seconds_Behind_Master'] < $delay);
		});

		return $slave ? $slave : $this->master;
	}

	protected function use_gtid()
	{
		if ($this->use_gtid === NULL)
		{
			$this->use_gtid = ($this->master
				->execute_query(GTID_MODE)->get() === 'ON');
		}

		return $this->use_gtid;
	}
}
