<?php
namespace SQL\DDL\Constraint;

use SQL\Column;
use SQL\DDL\Constraint;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * Generic FOREIGN KEY constraint. Some drivers do not support some features.
 *
 * @package     SQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/ansi-diff-foreign-keys.html MySQL
 * @link http://www.postgresql.org/docs/current/static/ddl-constraints.html#DDL-CONSTRAINTS-FK PostgreSQL
 * @link http://www.sqlite.org/foreignkeys.html SQLite
 * @link http://msdn.microsoft.com/library/ms175464.aspx Transact-SQL
 */
class Foreign extends Constraint
{
	/**
	 * @var array   Referenced columns
	 */
	public $columns;

	/**
	 * @var boolean|string  The time at which a deferred constraint should be checked
	 */
	protected $deferrable;

	/**
	 * @var string  Match type
	 */
	protected $match;

	/**
	 * @var array   Referential actions
	 */
	protected $on;

	/**
	 * @var array   Referencing columns
	 */
	public $referencing;

	/**
	 * @var Table   Referenced table
	 */
	public $table;

	/**
	 * @uses table()
	 * @uses columns()
	 *
	 * @param   array|string|Expression|Identifier  $table      Converted to Table
	 * @param   array                               $columns    List of columns, each converted to Column
	 */
	public function __construct($table = NULL, $columns = NULL)
	{
		parent::__construct();

		$this->columns      =& $this->parameters[':columns'];
		$this->referencing  =& $this->parameters[':referencing'];
		$this->table        =& $this->parameters[':table'];

		if ($table !== NULL)
		{
			$this->table($table);
		}

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = parent::__toString();

		if ($this->referencing)
		{
			$value .= 'FOREIGN KEY (:referencing) ';
		}

		$value .= 'REFERENCES :table';

		if ($this->columns)
		{
			$value .= ' (:columns)';
		}

		if ($this->match)
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			$value .= ' MATCH '.$this->match;
		}

		if ( ! empty($this->on['DELETE']))
		{
			$value .= ' ON DELETE '.$this->on['DELETE'];
		}

		if ( ! empty($this->on['UPDATE']))
		{
			$value .= ' ON UPDATE '.$this->on['UPDATE'];
		}

		if ($this->deferrable !== NULL)
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			if ($this->deferrable)
			{
				$value .= ' DEFERRABLE';

				if (is_string($this->deferrable))
				{
					$value .= ' INITIALLY '.$this->deferrable;
				}
			}
			else
			{
				$value .= ' NOT DEFERRABLE';
			}
		}

		return $value;
	}

	/**
	 * Append multiple referenced columns.
	 *
	 * @param   array   $columns    List of columns, each converted to Column, or NULL to reset
	 * @return  $this
	 */
	public function columns($columns)
	{
		if ($columns === NULL)
		{
			$this->columns = NULL;
		}
		else
		{
			foreach ($columns as $column)
			{
				if ( ! $column instanceof Expression
					AND ! $column instanceof Identifier)
				{
					$column = new Column($column);
				}

				$this->columns[] = $column;
			}
		}

		return $this;
	}

	/**
	 * Set whether or not the constraint can be deferred and when it should be
	 * checked.
	 *
	 * [!!] Not supported by MySQL nor SQL Server
	 *
	 * @param   boolean|string  $value  DEFERRED or IMMEDIATE
	 * @return  $this
	 */
	public function deferrable($value)
	{
		$this->deferrable = is_string($value) ? strtoupper($value) : $value;

		return $this;
	}

	/**
	 * Set the match type.
	 *
	 * [!!] Not supported by MySQL nor SQL Server
	 *
	 * @param   string  $value  FULL, PARTIAL, or SIMPLE
	 * @return  $this
	 */
	public function match($value)
	{
		$this->match = strtoupper($value);

		return $this;
	}

	/**
	 * Set a referential action.
	 *
	 * @param   string  $event  DELETE or UPDATE
	 * @param   string  $action CASCADE, RESTRICT, SET NULL, SET DEFAULT or NO ACTION
	 * @return  $this
	 */
	public function on($event, $action)
	{
		$this->on[strtoupper($event)] = strtoupper($action);

		return $this;
	}

	/**
	 * Append multiple referencing columns.
	 *
	 * @param   array   $columns    List of columns, each converted to Column, or NULL to reset
	 * @return  $this
	 */
	public function referencing($columns)
	{
		if ($columns === NULL)
		{
			$this->referencing = NULL;
		}
		else
		{
			foreach ($columns as $column)
			{
				if ( ! $column instanceof Expression
					AND ! $column instanceof Identifier)
				{
					$column = new Column($column);
				}

				$this->referencing[] = $column;
			}
		}

		return $this;
	}

	/**
	 * Set the referenced table.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @return  $this
	 */
	public function table($table)
	{
		if ( ! $table instanceof Expression AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		$this->table = $table;

		return $this;
	}
}
