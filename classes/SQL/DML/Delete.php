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
 * Generic DELETE statement. Some drivers do not support some features.
 *
 * @package     SQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/delete.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-delete.html PostgreSQL
 * @link http://www.sqlite.org/lang_delete.html SQLite
 * @link http://msdn.microsoft.com/library/ms189835.aspx Transact-SQL
 */
class Delete extends Expression
{
	/**
	 * @var Table|Alias Table from which to delete rows
	 */
	public $from;

	/**
	 * @var integer Maximum number of rows to delete
	 */
	public $limit;

	/**
	 * @var Table_Reference Table(s) referenced in the search conditions
	 */
	public $using;

	/**
	 * @var Conditions  Search conditions
	 */
	public $where;

	/**
	 * @uses from()
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 */
	public function __construct($table = NULL, $alias = NULL)
	{
		$this->from     =& $this->parameters[':table'];
		$this->limit    =& $this->parameters[':limit'];
		$this->using    =& $this->parameters[':using'];
		$this->where    =& $this->parameters[':where'];

		if ($table !== NULL)
		{
			$this->from($table, $alias);
		}
	}

	public function __toString()
	{
		$value = 'DELETE FROM :table';

		if ($this->using)
		{
			// Not allowed by MSSQL
			// Not allowed by SQLite
			$value .= ' USING :using';
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
	 * Set the table from which to delete rows, optionally assigning it an
	 * alias.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function from($table, $alias = NULL)
	{
		if ( ! $table instanceof Expression AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		if ($alias)
		{
			$table = new Alias($table, $alias);
		}

		$this->from = $table;

		return $this;
	}

	/**
	 * Set the maximum number of rows to be deleted.
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
	 * Set the table(s) referenced in the search conditions.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   array|string|Expression|Identifier|Table_Reference  $reference      Table_Reference or converted to Table
	 * @param   array|string|Expression|Identifier                  $table_alias    Table alias when converting to Table
	 * @return  $this
	 */
	public function using($reference, $table_alias = NULL)
	{
		if ( ! $reference instanceof Table_Reference)
		{
			$reference = new Table_Reference($reference, $table_alias);
		}

		$this->using = $reference;

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
