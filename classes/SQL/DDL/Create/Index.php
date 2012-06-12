<?php
namespace SQL\DDL;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * Generic CREATE INDEX statement.
 *
 * @package     SQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-index.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createindex.html PostgreSQL
 * @link http://www.sqlite.org/lang_createindex.html SQLite
 * @link http://msdn.microsoft.com/library/ms188783.aspx Transact-SQL
 */
class Create_Index extends Expression
{
	/**
	 * @var array   Columns or expressions to be included
	 */
	public $columns;

	/**
	 * @var Identifier  Name of the index
	 */
	public $name;

	/**
	 * @var Table   Table to be indexed
	 */
	public $on;

	/**
	 * @var Expression  Type of index
	 */
	public $type;

	/**
	 * @uses columns()
	 * @uses name()
	 * @uses on()
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Identifier
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array $columns                              List of columns, each converted to Column
	 */
	public function __construct($name = NULL, $table = NULL, $columns = NULL)
	{
		$this->columns  =& $this->parameters[':columns'];
		$this->name     =& $this->parameters[':name'];
		$this->on       =& $this->parameters[':table'];
		$this->type     =& $this->parameters[':type'];

		if ($name !== NULL)
		{
			$this->name($name);
		}

		if ($table !== NULL)
		{
			$this->on($table);
		}

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->type)
		{
			$value .= ' :type';
		}

		$value .= ' INDEX :name ON :table (:columns)';

		return $value;
	}

	/**
	 * Append one column or expression to be included in the index.
	 *
	 * @param   array|string|Expression|Identifier  $column     Converted to Column
	 * @param   string                              $direction  Direction to sort, ASC or DESC
	 * @return  $this
	 */
	public function column($column, $direction = NULL)
	{
		if ( ! $column instanceof Expression
			AND ! $column instanceof Identifier)
		{
			$column = new Column($column);
		}

		if ($direction)
		{
			$column = new Expression(
				'? '.strtoupper($direction), array($column)
			);
		}

		$this->columns[] = $column;

		return $this;
	}

	/**
	 * Append columns and/or expressions to be included in the index.
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
	 * Set the name of the index to be created.
	 *
	 * @param   array|string|Expression|Identifier  $value  Converted to Identifier
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof Expression AND ! $value instanceof Identifier)
		{
			$value = new Identifier($value);
		}

		$this->name = $value;

		return $this;
	}

	/**
	 * Set the table to be indexed.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @return  $this
	 */
	public function on($table)
	{
		if ( ! $table instanceof Expression AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		$this->on = $table;

		return $this;
	}

	/**
	 * Set whether or not duplicate values should be prohibited in the index.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function unique($value = TRUE)
	{
		$this->type = $value ? new Expression('UNIQUE') : NULL;

		return $this;
	}
}
