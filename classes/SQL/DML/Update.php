<?php
namespace SQL\DML;

use SQL\Alias;
use SQL\Column;
use SQL\Conditions;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;
use SQL\Table_Reference;

/**
 * Generic UPDATE statement. Some drivers do not support some features.
 *
 * @package     SQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/update.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-update.html PostgreSQL
 * @link http://www.sqlite.org/lang_update.html SQLite
 * @link http://msdn.microsoft.com/library/ms177523.aspx Transact-SQL
 */
class Update extends Expression
{
	/**
	 * @var Table_Reference Table(s) referenced in the search conditions
	 */
	public $from;

	/**
	 * @var integer Maximum number of rows to update
	 */
	public $limit;

	/**
	 * @var array   List of column assignments
	 */
	public $set;

	/**
	 * @var Table|Alias Table in which to update rows
	 */
	public $table;

	/**
	 * @var Conditions  Search conditions
	 */
	public $where;

	/**
	 * @uses table()
	 * @uses set()
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @param   array                               $values Hash of (column => value) assignments
	 */
	public function __construct($table = NULL, $alias = NULL, $values = NULL)
	{
		$this->from     =& $this->parameters[':from'];
		$this->limit    =& $this->parameters[':limit'];
		$this->set      =& $this->parameters[':values'];
		$this->table    =& $this->parameters[':table'];
		$this->where    =& $this->parameters[':where'];

		if ($table !== NULL)
		{
			$this->table($table, $alias);
		}

		$this->set($values);
	}

	public function __toString()
	{
		$value = 'UPDATE :table SET :values';

		if ($this->from)
		{
			// Not allowed by MySQL
			// Not allowed by SQLite
			$value .= ' FROM :from';
		}

		if ($this->where)
		{
			$value .= ' WHERE :where';
		}

		if ($this->limit !== NULL)
		{
			// Not allowed by MSSQL
			// Not allowed by PostgreSQL
			$value .= ' LIMIT :limit';
		}

		return $value;
	}

	/**
	 * Set the table(s) referenced in the search conditions.
	 *
	 * [!!] Not supported by MySQL nor SQLite
	 *
	 * @param   array|string|Expression|Identifier|Table_Reference  $reference      Table_Reference or converted to Table
	 * @param   array|string|Expression|Identifier                  $table_alias    Table alias when converting to Table
	 * @return  $this
	 */
	public function from($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof Table_Reference)
		{
			$reference = new Table_Reference($reference, $table_alias);
		}

		$this->from = $reference;

		return $this;
	}

	/**
	 * Set the maximum number of rows to be updated.
	 *
	 * [!!] Not supported by PostgreSQL
	 *
	 * @param   integer $count  Number of rows or NULL to reset
	 * @return  $this
	 */
	public function limit($count)
	{
		$this->limit = $count;

		return $this;
	}

	/**
	 * Append multiple column assignments.
	 *
	 * @param   array   $values Hash of (column => value) assignments or NULL to reset
	 * @return  $this
	 */
	public function set($values)
	{
		if ($values === NULL)
		{
			$this->set = NULL;
		}
		else
		{
			foreach ($values as $column => $value)
			{
				$column = new Column($column);

				$this->set[] = new Expression(
					'? = ?', array($column, $value)
				);
			}
		}

		return $this;
	}

	/**
	 * Set the table in which to update rows.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function table($table, $alias = NULL)
	{
		if ( ! $table instanceof Expression AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		if ($alias)
		{
			$table = new Alias($table, $alias);
		}

		$this->table = $table;

		return $this;
	}

	/**
	 * Append a column assignment.
	 *
	 * @param   array|string|Expression|Identifier  $column Converted to Column
	 * @param   mixed                               $value  Value assigned to the column
	 * @return  $this
	 */
	public function value($column, $value)
	{
		if ( ! $column instanceof Expression
			AND ! $column instanceof Identifier)
		{
			$column = new Column($column);
		}

		$this->set[] = new Expression('? = ?', array($column, $value));

		return $this;
	}

	/**
	 * Set the search condition(s). When no operator is specified, the first
	 * argument is used directly.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function where($left_column, $operator = NULL, $right = NULL)
	{
		if ($operator !== NULL)
		{
			if ( ! $left_column instanceof Expression
				AND ! $left_column instanceof Identifier)
			{
				$left_column = new Column($left_column);
			}

			$left_column = new Conditions($left_column, $operator, $right);
		}

		$this->where = $left_column;

		return $this;
	}
}
