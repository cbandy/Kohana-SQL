<?php
namespace SQL;

/**
 * Expression for building a table reference. Some drivers do not support some
 * features.
 *
 * @package     SQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Table_Reference extends Expression
{
	/**
	 * @var bool    Whether or not the (sub-)expression has just begun
	 */
	protected $empty = TRUE;

	/**
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 */
	public function __construct($table = NULL, $alias = NULL)
	{
		parent::__construct('');

		if ($table !== NULL)
		{
			$this->add_reference(NULL, $table, $alias);
		}
	}

	/**
	 * Add a table reference using a separator when necessary.
	 *
	 * @param   string                              $glue   Comma or JOIN
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	protected function add_reference($glue, $table, $alias)
	{
		if ( ! $this->empty)
		{
			$this->value .= $glue.' ';
		}

		if ( ! $table instanceof Expression AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		$this->empty = FALSE;
		$this->parameters[] = $table;
		$this->value .= ($table instanceof Expression) ? '(?)' : '?';

		if ($alias)
		{
			if ( ! $alias instanceof Expression
				AND ! $alias instanceof Identifier)
			{
				$alias = new Identifier($alias);
			}

			$this->parameters[] = $alias;
			$this->value .= ' AS ?';
		}

		return $this;
	}

	/**
	 * Open parenthesis.
	 *
	 * @return  $this
	 */
	public function open()
	{
		if ( ! $this->empty)
		{
			$this->value .= ', ';
		}

		$this->empty = TRUE;
		$this->value .= '(';

		return $this;
	}

	/**
	 * Close parenthesis.
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
	 * Add a table or query.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function add($table, $alias = NULL)
	{
		return $this->add_reference(',', $table, $alias);
	}

	/**
	 * Join a table or query.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @param   string                              $type   Join type (e.g., INNER)
	 * @return  $this
	 */
	public function join($table, $alias = NULL, $type = NULL)
	{
		if ($type)
		{
			$type = ' '.strtoupper($type);
		}

		return $this->add_reference($type.' JOIN', $table, $alias);
	}

	/**
	 * Set the join condition(s). When no operator is specified, the first
	 * argument is used directly.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function on($left_column, $operator = NULL, $right_column = NULL)
	{
		if ($operator !== NULL)
		{
			if ( ! $left_column instanceof Expression
				AND ! $left_column instanceof Identifier)
			{
				$left_column = new Column($left_column);
			}

			if ( ! $right_column instanceof Expression
				AND ! $right_column instanceof Identifier)
			{
				$right_column = new Column($right_column);
			}

			$left_column = new Conditions(
				$left_column, $operator, $right_column
			);
		}

		$this->empty = FALSE;
		$this->parameters[] = $left_column;
		$this->value .= ' ON (?)';

		return $this;
	}

	/**
	 * Set the join columns.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array   $columns    List of columns, each converted to Column
	 * @return  $this
	 */
	public function using($columns)
	{
		$result = array();

		foreach ($columns as $column)
		{
			if ( ! $column instanceof Expression
				AND ! $column instanceof Identifier)
			{
				$column = new Column($column);
			}

			$result[] = $column;
		}

		$this->empty = FALSE;
		$this->parameters[] = $result;
		$this->value .= ' USING (?)';

		return $this;
	}


	// Helpers

	/**
	 * Cross join a table or query.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function cross_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'CROSS');
	}

	/**
	 * Full join a table or query.
	 *
	 * [!!] Not supported by MySQL nor SQLite
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'FULL');
	}

	/**
	 * Inner join a table or query.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function inner_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'INNER');
	}

	/**
	 * Left join a table or query.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'LEFT');
	}

	/**
	 * Naturally full join a table or query.
	 *
	 * [!!] Not supported by MySQL, SQLite nor SQL Server
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function natural_full_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL FULL');
	}

	/**
	 * Naturally inner join a table or query.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function natural_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL');
	}

	/**
	 * Naturally left join a table or query.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function natural_left_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL LEFT');
	}

	/**
	 * Naturally right join a table or query.
	 *
	 * [!!] Not supported by SQLite nor SQL Server
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function natural_right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'NATURAL RIGHT');
	}

	/**
	 * Right join a table or query.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @param   array|string|Expression|Identifier  $alias  Converted to Identifier
	 * @return  $this
	 */
	public function right_join($table, $alias = NULL)
	{
		return $this->join($table, $alias, 'RIGHT');
	}
}
