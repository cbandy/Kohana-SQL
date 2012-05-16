<?php
namespace SQL\DML;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;

/**
 * Generic statement for combining queries using the UNION, INTERSECT and EXCEPT
 * operators. Some drivers do not support some features.
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
class Set extends Expression
{
	/**
	 * @var bool    Whether or not the (sub-)expression has just begun
	 */
	protected $empty = TRUE;

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
	 * @uses SQL_DML_Set::add()
	 *
	 * @param   SQL_Expression  $query
	 */
	public function __construct($query = NULL)
	{
		$this->value = '';

		$this->limit =& $this->parameters[':limit'];
		$this->offset =& $this->parameters[':offset'];
		$this->order_by =& $this->parameters[':orderby'];

		if ($query !== NULL)
		{
			$this->add(NULL, $query);
		}
	}

	public function __toString()
	{
		$value = $this->value;

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
	 * Open parenthesis using a combination operator when necessary, optionally
	 * adding another query.
	 *
	 * @param   string      $operator   EXCEPT, INTERSECT, or UNION
	 * @param   Expression  $query
	 * @return  $this
	 */
	public function open($operator, $query = NULL)
	{
		if ( ! $this->empty)
		{
			$this->value .= ' '.strtoupper($operator).' ';
		}

		$this->empty = TRUE;
		$this->value .= '(';

		if ($query !== NULL)
		{
			$this->add(NULL, $query);
		}

		return $this;
	}

	/**
	 * Close parenthesis
	 *
	 * @return  $this
	 */
	public function close()
	{
		$this->empty = FALSE;
		$this->value .= ')';

		return $this;
	}

	/**
	 * Add a query using a combination operator when necessary.
	 *
	 * @param   string      $operator   EXCEPT, INTERSECT, or UNION
	 * @param   Expression  $query
	 * @return  $this
	 */
	public function add($operator, $query)
	{
		if ( ! $this->empty)
		{
			$this->value .= ' '.strtoupper($operator).' ';
		}

		$this->empty = FALSE;
		$this->parameters[] = $query;
		$this->value .= '(?)';

		return $this;
	}

	/**
	 * Add a query using EXCEPT.
	 *
	 * [!!] Not supported by MySQL
	 *
	 * @param   Expression  $query
	 * @param   boolean     $all    Allow duplicate rows
	 * @return  $this
	 */
	public function except($query, $all = FALSE)
	{
		return $this->add($all ? 'EXCEPT ALL' : 'EXCEPT', $query);
	}

	/**
	 * Open a parenthesis using EXCEPT, optionally adding another query.
	 *
	 * [!!] Not supported by MySQL
	 *
	 * @param   Expression  $query
	 * @param   boolean     $all    Allow duplicate rows
	 * @return  $this
	 */
	public function except_open($query = NULL, $all = FALSE)
	{
		return $this->open($all ? 'EXCEPT ALL' : 'EXCEPT', $query);
	}

	/**
	 * Add a query using INTERSECT.
	 *
	 * [!!] Not supported by MySQL
	 *
	 * @param   Expression  $query
	 * @param   boolean     $all    Allow duplicate rows
	 * @return  $this
	 */
	public function intersect($query, $all = FALSE)
	{
		return $this->add($all ? 'INTERSECT ALL' : 'INTERSECT', $query);
	}

	/**
	 * Open a parenthesis using INTERSECT, optionally adding another query.
	 *
	 * [!!] Not supported by MySQL
	 *
	 * @param   Expression  $query
	 * @param   boolean     $all    Allow duplicate rows
	 * @return  $this
	 */
	public function intersect_open($query = NULL, $all = FALSE)
	{
		return $this->open($all ? 'INTERSECT ALL' : 'INTERSECT', $query);
	}

	/**
	 * Set the maximum number of rows to retrieve.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   integer $count  Number of rows or NULL to reset
	 * @return  $this
	 */
	public function limit($count)
	{
		$this->parameters[':limit'] = $count;

		return $this;
	}

	/**
	 * Set the number of rows to skip.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   integer $start  Number of rows
	 * @return  $this
	 */
	public function offset($start)
	{
		$this->parameters[':offset'] = $start;

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
	 * Add a query using UNION.
	 *
	 * @param   Expression  $query
	 * @param   boolean     $all    Allow duplicate rows
	 * @return  $this
	 */
	public function union($query, $all = FALSE)
	{
		return $this->add($all ? 'UNION ALL' : 'UNION', $query);
	}

	/**
	 * Open a parenthesis using UNION, optionally adding another query.
	 *
	 * @param   Expression  $query
	 * @param   boolean     $all    Allow duplicate rows
	 * @return  $this
	 */
	public function union_open($query = NULL, $all = FALSE)
	{
		return $this->open($all ? 'UNION ALL' : 'UNION', $query);
	}
}
