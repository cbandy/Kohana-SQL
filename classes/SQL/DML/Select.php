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
 * Generic SELECT statement.
 *
 * @package     SQL
 * @category    Queries
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/select.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-select.html PostgreSQL
 * @link http://www.sqlite.org/lang_select.html SQLite
 * @link http://msdn.microsoft.com/library/ms189499.aspx Transact-SQL
 */
class Select extends Expression
{
	/**
	 * @var boolean Whether or not rows should be unique
	 */
	public $distinct;

	/**
	 * @var Table_Reference Tables from which to retrieve rows
	 */
	public $from;

	/**
	 * @var array   Columns and/or Expressions by which rows should be grouped
	 */
	public $group_by;

	/**
	 * @var Conditions  Group search conditions
	 */
	public $having;

	/**
	 * @var integer Maximum number of rows to retrieve
	 */
	public $limit;

	/**
	 * @var integer Number of rows to skip
	 */
	public $offset;

	/**
	 * @var array   Columns and/or Expressions by which rows should be ordered
	 */
	public $order_by;

	/**
	 * @var array   Columns, Expressions and/or values to be selected
	 */
	public $values;

	/**
	 * @var Conditions  Search conditions
	 */
	public $where;

	/**
	 * @uses columns()
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs
	 */
	public function __construct($columns = NULL)
	{
		$this->from     =& $this->parameters[':from'];
		$this->group_by =& $this->parameters[':groupby'];
		$this->having   =& $this->parameters[':having'];
		$this->limit    =& $this->parameters[':limit'];
		$this->offset   =& $this->parameters[':offset'];
		$this->order_by =& $this->parameters[':orderby'];
		$this->values   =& $this->parameters[':values'];
		$this->where    =& $this->parameters[':where'];

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = 'SELECT';

		if ($this->distinct)
		{
			$value .= ' DISTINCT';
		}

		$value .= $this->values ? ' :values' : ' *';

		if ($this->from)
		{
			$value .= ' FROM :from';
		}

		if ($this->where)
		{
			$value .= ' WHERE :where';
		}

		if ($this->group_by)
		{
			$value .= ' GROUP BY :groupby';
		}

		if ($this->having)
		{
			$value .= ' HAVING :having';
		}

		if ($this->order_by)
		{
			$value .= ' ORDER BY :orderby';
		}

		if ($this->limit !== NULL)
		{
			// Not allowed by MSSQL
			$value .= ' LIMIT :limit';
		}

		if ($this->offset)
		{
			// LIMIT required by MySQL and SQLite
			// Not allowed by MSSQL
			$value .= ' OFFSET :offset';
		}

		return $value;
	}

	/**
	 * Append one column or expression to be selected.
	 *
	 * @param   array|string|Expression|Identifier  $column Converted to Column
	 * @param   string                              $alias  Column alias
	 * @return  $this
	 */
	public function column($column, $alias = NULL)
	{
		if ( ! $column instanceof Expression
			AND ! $column instanceof Identifier)
		{
			$column = new Column($column);
		}

		if ($alias)
		{
			$column = new Alias($column, $alias);
		}

		$this->values[] = $column;

		return $this;
	}

	/**
	 * Append multiple columns and/or expressions to be selected.
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs or NULL to reset
	 * @return  $this
	 */
	public function columns($columns)
	{
		if ($columns === NULL)
		{
			$this->values = NULL;
		}
		else
		{
			foreach ($columns as $alias => $column)
			{
				if ( ! $column instanceof Expression
					AND ! $column instanceof Identifier)
				{
					$column = new Column($column);
				}

				if (is_string($alias) AND $alias)
				{
					$column = new Alias($column, $alias);
				}

				$this->values[] = $column;
			}
		}

		return $this;
	}

	/**
	 * Set whether or not retrieved rows should be unique.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function distinct($value = TRUE)
	{
		$this->distinct = $value;

		return $this;
	}

	/**
	 * Set the table(s) from which to retrieve rows.
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
	 * Append multiple columns and/or expressions by which rows should be
	 * grouped.
	 *
	 * @param   array   $columns    List of columns, each converted to Column, or NULL to reset
	 * @return  $this
	 */
	public function group_by($columns)
	{
		if ($columns === NULL)
		{
			$this->group_by = NULL;
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

				$this->group_by[] = $column;
			}
		}

		return $this;
	}

	/**
	 * Set the group search condition(s). When no operator is specified, the
	 * first argument is used directly.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function having($left_column, $operator = NULL, $right = NULL)
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

		$this->having = $left_column;

		return $this;
	}

	/**
	 * Set the maximum number of rows to retrieve.
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
	 * Set the number of rows to skip.
	 *
	 * @param   integer $start  Number of rows
	 * @return  $this
	 */
	public function offset($start)
	{
		$this->offset = $start;

		return $this;
	}

	/**
	 * Append a column or expression by which rows should be sorted.
	 *
	 * @param   array|string|Expression|Identifier  $column     Converted to Column or NULL to reset
	 * @param   string|Expression                   $direction  Direction of sort
	 * @return  $this
	 */
	public function order_by($column, $direction = NULL)
	{
		if ($column === NULL)
		{
			$this->order_by = NULL;
		}
		else
		{
			if ( ! $column instanceof Expression
				AND ! $column instanceof Identifier)
			{
				$column = new Column($column);
			}

			if ($direction)
			{
				$column = ($direction instanceof Expression)
					? new Expression('? ?', array($column, $direction))
					: new Expression('? '.strtoupper($direction), array($column));
			}

			$this->order_by[] = $column;
		}

		return $this;
	}

	/**
	 * Append one literal value or expression to be selected.
	 *
	 * @param   mixed|Expression                    $value  Literal value to append
	 * @param   array|string|Expression|Identifier  $alias  Value alias, converted to Identifier
	 * @return  $this
	 */
	public function value($value, $alias = NULL)
	{
		if ($alias)
		{
			$value = new Alias($value, $alias);
		}

		$this->values[] = $value;

		return $this;
	}

	/**
	 * Append multiple literal values and/or expressions to be selected.
	 *
	 * @param   array   $values Hash of (alias => value) pairs or NULL to reset
	 * @return  $this
	 */
	public function values($values)
	{
		if ($values === NULL)
		{
			$this->values = NULL;
		}
		else
		{
			foreach ($values as $alias => $value)
			{
				if (is_string($alias) AND $alias)
				{
					$value = new Alias($value, $alias);
				}

				$this->values[] = $value;
			}
		}

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
