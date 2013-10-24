<?php
namespace SQL;

class Replication_Executor implements Executor
{
	const EVENT  = 'EVENT';
	const LAX    = 'LAX';
	const STRICT = 'STRICT';
	const TIME   = 'TIME';

	/**
	 * @var Replication_Cluster
	 */
	protected $cluster;

	/**
	 * @var mixed   Tolerance threshold used when executing a query
	 */
	protected $threshold;

	/**
	 * @var string  Method used to execute a query
	 */
	protected $tolerance;

	/**
	 * @uses tolerance()
	 *
	 * @param   Replication_Cluster $cluster
	 * @param   string              $tolerance
	 * @param   mixed               $threshold
	 */
	public function __construct($cluster, $tolerance = STRICT, $threshold = NULL)
	{
		$this->cluster = $cluster;
		$this->tolerance($tolerance, $threshold);
	}

	public function execute_command($statement)
	{
		return $this->cluster->master()->execute_command($statement);
	}

	public function execute_query($statement)
	{
		return $this->cluster->{$this->tolerance}($statement, $this->threshold);
	}

	/**
	 * Set the tolerance used by execute_query().
	 *
	 * @param   string  $tolerance
	 * @param   mixed   $threshold
	 * @return  $this
	 */
	public function tolerance($tolerance, $threshold = NULL)
	{
		static $methods = array(
			EVENT   => 'execute_query_tolerant_of_event',
			LAX     => 'execute_query_tolerant',
			STRICT  => 'execute_query_strict',
			TIME    => 'execute_query_tolerant_of_time',
		);

		$this->threshold = $threshold;
		$this->tolerance = $methods[$tolerance];

		return $this;
	}
}
