<?php
namespace SQL\PostgreSQL\DDL;

use SQL\Column as SQL_Column;
use SQL\Conditions;
use SQL\DDL\Create_Index as SQL_Create_Index;
use SQL\Expression;
use SQL\Identifier;

/**
 * CREATE INDEX statement for PostgreSQL.
 *
 * @package     SQL
 * @subpackage  PostgreSQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-createindex.html
 */
class Create_Index extends SQL_Create_Index
{
	/**
	 * @var Identifier  Tablespace in which to create the index
	 */
	public $tablespace;

	/**
	 * @var string  Index method
	 */
	public $using;

	/**
	 * @var Conditions  Conditions for which a row is included in the partial
	 *  index
	 */
	public $where;

	/**
	 * @var Parameters  Storage parameters for the index method
	 */
	public $with;

	public function __construct($name = NULL, $table = NULL, $columns = NULL)
	{
		parent::__construct($name, $table, $columns);

		$this->tablespace   =& $this->parameters[':tablespace'];
		$this->where        =& $this->parameters[':where'];
		$this->with         =& $this->parameters[':with'];
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->type)
		{
			$value .= ' :type';
		}

		$value .= ' INDEX :name ON :table';

		if ($this->using)
		{
			$value .= ' USING '.$this->using;
		}

		$value .= ' (:columns)';

		if ($this->with)
		{
			$value .= ' WITH (:with)';
		}

		if ($this->tablespace)
		{
			$value .= ' TABLESPACE :tablespace';
		}

		if ($this->where)
		{
			$value .= ' WHERE :where';
		}

		return $value;
	}

	/**
	 * Append one column or expression to be included in the index.
	 *
	 * @param   array|string|Expression|Identifier  $column     Converted to SQL\Column
	 * @param   string                              $direction  Direction to sort, ASC or DESC
	 * @param   string                              $nulls      Position to which NULL values should sort, FIRST or LAST
	 * @return  $this
	 */
	public function column($column, $direction = NULL, $nulls = NULL)
	{
		if ($column instanceof Expression)
		{
			// Wrap expression in parentheses
			$column = new Expression('(?)', array($column));
		}
		elseif ( ! $column instanceof Identifier)
		{
			$column = new SQL_Column($column);
		}

		if ($direction OR $nulls)
		{
			if ( ! $column instanceof Expression)
			{
				$column = new Expression('?', array($column));
			}

			if ($direction)
			{
				$column->value .= ' '.strtoupper($direction);
			}

			if ($nulls)
			{
				$column->value .= ' NULLS '.strtoupper($nulls);
			}
		}

		$this->columns[] = $column;

		return $this;
	}

	/**
	 * Set the tablespace in which to create the index.
	 *
	 * @param   array|string|Expression|Identifier  $value  Converted to Identifier
	 * @return  $this
	 */
	public function tablespace($value)
	{
		if ($value !== NULL
			AND ! $value instanceof Expression
			AND ! $value instanceof Identifier)
		{
			$value = new Identifier($value);
		}

		$this->tablespace = $value;

		return $this;
	}

	/**
	 * Set the index method.
	 *
	 * @param   string  $method btree, hash, gist, gin, etc.
	 * @return  $this
	 */
	public function using($method)
	{
		$this->using = $method;

		return $this;
	}

	/**
	 * Set the conditions for which a row is included in the partial index. When
	 * no operator is specified, the first argument is used directly.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to SQL\Column
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
				$left_column = new SQL_Column($left_column);
			}

			$left_column = new Conditions($left_column, $operator, $right);
		}

		$this->where = $left_column;

		return $this;
	}

	/**
	 * Set storage parameters for the index method.
	 *
	 * @param   array|Parameters    $parameters Hash of (parameter => value) pairs
	 * @return  $this
	 */
	public function with($parameters)
	{
		if (is_array($parameters))
		{
			$parameters = new Parameters($parameters);
		}

		$this->with = $parameters;

		return $this;
	}
}
