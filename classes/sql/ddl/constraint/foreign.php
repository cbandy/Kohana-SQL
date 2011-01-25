<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/ansi-diff-foreign-keys.html MySQL
 * @link http://www.postgresql.org/docs/current/static/ddl-constraints.html#DDL-CONSTRAINTS-FK PostgreSQL
 * @link http://www.sqlite.org/foreignkeys.html SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms175464.aspx Transact-SQL
 */
class SQL_DDL_Constraint_Foreign extends SQL_DDL_Constraint
{
	/**
	 * @var boolean|string  The time at which a deferred constraint should be checked
	 */
	protected $_deferrable;

	/**
	 * @var string  Match type
	 */
	protected $_match;

	/**
	 * @var array   Referential actions
	 */
	protected $_on;

	/**
	 * @uses SQL_DDL_Constraint_Foreign::table()
	 * @uses SQL_DDL_Constraint_Foreign::columns()
	 *
	 * @param   mixed   $table      Converted to SQL_Table
	 * @param   array   $columns    Each element converted to SQL_Column
	 */
	public function __construct($table = NULL, $columns = array())
	{
		parent::__construct('REFERENCES :table');

		if ($table !== NULL)
		{
			$this->table($table);
		}

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = parent::__toString();

		if ( ! empty($this->parameters[':referencing']))
		{
			$value .= 'FOREIGN KEY (:referencing) ';
		}

		$value .= $this->_value;

		if ( ! empty($this->parameters[':columns']))
		{
			$value .= ' (:columns)';
		}

		if ( ! empty($this->_match))
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			$value .= ' MATCH '.$this->_match;
		}

		if ( ! empty($this->_on['DELETE']))
		{
			$value .= ' ON DELETE '.$this->_on['DELETE'];
		}

		if ( ! empty($this->_on['UPDATE']))
		{
			$value .= ' ON UPDATE '.$this->_on['UPDATE'];
		}

		if (isset($this->_deferrable))
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			if (empty($this->_deferrable))
			{
				$value .= ' NOT DEFERRABLE';
			}
			else
			{
				$value .= ' DEFERRABLE';

				if (is_string($this->_deferrable))
				{
					$value .= ' INITIALLY '.$this->_deferrable;
				}
			}
		}

		return $value;
	}

	/**
	 * Set the referenced columns
	 *
	 * @param   array   $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function columns($columns)
	{
		foreach ($columns as & $column)
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}
		}

		$this->parameters[':columns'] = $columns;

		return $this;
	}

	/**
	 * Set whether or not the constraint can be deferred and when it should be checked
	 *
	 * @param   boolean|string  DEFERRED or IMMEDIATE
	 * @return  $this
	 */
	public function deferrable($value)
	{
		$this->_deferrable = is_string($value) ? strtoupper($value) : $value;

		return $this;
	}

	/**
	 * Set the match type
	 *
	 * @param   string  $value  FULL, PARTIAL, or SIMPLE
	 * @return  $this
	 */
	public function match($value)
	{
		$this->_match = strtoupper($value);

		return $this;
	}

	/**
	 * Set a referential action
	 *
	 * @param   string  $event  DELETE or UPDATE
	 * @param   string  $action CASCADE, RESTRICT, SET NULL, SET DEFAULT or NO ACTION
	 * @return  $this
	 */
	public function on($event, $action)
	{
		$this->_on[strtoupper($event)] = strtoupper($action);

		return $this;
	}

	/**
	 * Set the referencing columns
	 *
	 * @param   array   $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function referencing($columns)
	{
		foreach ($columns as & $column)
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}
		}

		$this->parameters[':referencing'] = $columns;

		return $this;
	}

	/**
	 * Set the referenced table
	 *
	 * @param   mixed   $table  Converted to SQL_Table
	 * @return  $this
	 */
	public function table($table)
	{
		if ( ! $table instanceof SQL_Expression
			AND ! $table instanceof SQL_Identifier)
		{
			$table = new SQL_Table($table);
		}

		$this->parameters[':table'] = $table;

		return $this;
	}
}