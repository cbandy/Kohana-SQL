<?php
namespace SQL\DML;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;
use SQL\Values;

/**
 * Generic INSERT statement.
 *
 * @package     SQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/insert.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-insert.html PostgreSQL
 * @link http://www.sqlite.org/lang_insert.html SQLite
 * @link http://msdn.microsoft.com/library/ms174335.aspx Transact-SQL
 */
class Insert extends Expression
{
	/**
	 * @var array   Columns to be populated with values
	 */
	public $columns;

	/**
	 * @var Table   Table in which to insert rows
	 */
	public $into;

	/**
	 * @var Values  Rows to be inserted
	 */
	public $values;

	/**
	 * @uses into()
	 * @uses columns()
	 *
	 * @param   array|string|Expression|Identifier  $table      Converted to Table
	 * @param   array                               $columns    List of columns, each converted to Column
	 */
	public function __construct($table = NULL, $columns = NULL)
	{
		$this->columns  =& $this->parameters[':columns'];
		$this->into     =& $this->parameters[':table'];
		$this->values   =& $this->parameters[':values'];

		if ($table !== NULL)
		{
			$this->into($table);
		}

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = 'INSERT INTO :table ';

		if ($this->columns)
		{
			$value .= '(:columns) ';
		}

		if ($this->values)
		{
			$value .= ':values';
		}
		else
		{
			// Not allowed by MySQL
			$value .= 'DEFAULT VALUES';
		}

		return $value;
	}

	/**
	 * Append multiple columns to be populated with values.
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
	 * Set the table in which to insert rows.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @return  $this
	 */
	public function into($table)
	{
		if ( ! $table instanceof Expression AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		$this->into = $table;

		return $this;
	}

	/**
	 * Set the row(s) of values to be inserted.
	 *
	 * @param   array|Values    $values Row or rows of values
	 * @return  $this
	 */
	public function values($values)
	{
		if (is_array($values))
		{
			$values = new Values($values);
		}

		$this->values = $values;

		return $this;
	}
}
