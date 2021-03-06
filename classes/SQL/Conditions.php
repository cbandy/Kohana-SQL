<?php
namespace SQL;

/**
 * Expression for building and combining predicates.
 *
 * @package     SQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Conditions extends Expression
{
	/**
	 * @var bool    Whether or not the (sub)expression has just begun
	 */
	protected $empty = TRUE;

	/**
	 * @var string  The content of the previous open() operation
	 */
	protected $open = '';

	/**
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 */
	public function __construct($left = NULL, $operator = NULL, $right = NULL)
	{
		parent::__construct('');

		if ($left !== NULL OR $operator !== NULL)
		{
			$this->add(NULL, $left, $operator, $right);
		}
	}

	/**
	 * Add a condition using a logical operator when necessary.
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function add($logic, $left, $operator = NULL, $right = NULL)
	{
		if ( ! $this->empty)
		{
			// Only append the logical operator between conditions
			$this->value .= ' '.strtoupper($logic).' ';
		}

		$this->empty = FALSE;
		$this->parameters[] = $left;
		$this->value .= '?';

		if ($operator)
		{
			$this->add_rhs($operator, $right);
		}

		return $this;
	}

	/**
	 * Append an operator and process its right operand.
	 *
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  void
	 */
	protected function add_rhs($operator, $right)
	{
		// SQL operators are always uppercase
		$operator = strtoupper($operator);

		$this->value .= ' '.$operator.' ';

		if ($operator === 'IN')
		{
			$this->parameters[] = $right;
			$this->value .= '(?)';
		}
		elseif ($operator === 'BETWEEN' AND is_array($right))
		{
			// BETWEEN always has exactly two arguments
			list($min, $max) = $right;

			$this->parameters[] = $min;
			$this->parameters[] = $max;
			$this->value .= '? AND ?';
		}
		else
		{
			$this->parameters[] = $right;
			$this->value .= '?';
		}
	}

	/**
	 * Append a unary condition using a logical operator when necessary.
	 *
	 * @param   string  $logic          Logical operator
	 * @param   string  $operator       Unary operator
	 * @param   mixed   $operand        Operand
	 * @param   string  $placeholder    Text to use for the positional placeholder
	 * @return  void
	 */
	protected function add_unary($logic, $operator, $operand, $placeholder)
	{
		if ( ! $this->empty)
		{
			// Only append the logical operator between conditions
			$this->value .= ' '.strtoupper($logic).' ';
		}

		$this->empty = FALSE;
		$this->parameters[] = $operand;
		$this->value .= $operator.' '.$placeholder;
	}

	/**
	 * Add a condition while converting the LHS to a column.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Expression
			AND ! $left_column instanceof Identifier)
		{
			$left_column = new Column($left_column);
		}

		return $this->add($logic, $left_column, $operator, $right);
	}

	/**
	 * Add a condition while converting both operands to columns.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Expression
			AND ! $right_column instanceof Identifier)
		{
			$right_column = new Column($right_column);
		}

		return $this->column($logic, $left_column, $operator, $right_column);
	}

	/**
	 * Add an EXISTS condition.
	 *
	 * @param   string  $logic  Logical operator
	 * @param   mixed   $query  Converted to Expression
	 * @return  $this
	 */
	public function exists($logic, $query)
	{
		if ( ! $query instanceof Expression)
		{
			$query = new Expression($query);
		}

		$this->add_unary($logic, 'EXISTS', $query, '(?)');

		return $this;
	}

	/**
	 * Add a negated condition using a logical operator when necessary.
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function not($logic, $left, $operator = NULL, $right = NULL)
	{
		if ( ! $this->empty)
		{
			// Only append the logical operator between conditions
			$this->value .= ' '.strtoupper($logic).' ';
		}

		$this->empty = FALSE;
		$this->parameters[] = $left;
		$this->value .= 'NOT ?';

		if ($operator)
		{
			$this->add_rhs($operator, $right);
		}

		return $this;
	}

	/**
	 * Add a negated condition while converting the LHS to a column.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function not_column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Expression
			AND ! $left_column instanceof Identifier)
		{
			$left_column = new Column($left_column);
		}

		return $this->not($logic, $left_column, $operator, $right);
	}

	/**
	 * Add a negated condition while converting both operands to columns.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function not_columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Expression
			AND ! $right_column instanceof Identifier)
		{
			$right_column = new Column($right_column);
		}

		return $this->not_column($logic, $left_column, $operator, $right_column);
	}

	/**
	 * Add a NOT EXISTS condition.
	 *
	 * @param   string  $logic  Logical operator
	 * @param   mixed   $query  Converted to Expression
	 * @return  $this
	 */
	public function not_exists($logic, $query)
	{
		if ( ! $query instanceof Expression)
		{
			$query = new Expression($query);
		}

		$this->add_unary($logic, 'NOT EXISTS', $query, '(?)');

		return $this;
	}

	/**
	 * Open a negated parenthesis using a logical operator when necessary,
	 * optionally adding another condition.
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function not_open($logic, $left = NULL, $operator = NULL, $right = NULL)
	{
		$this->open = 'NOT (';

		if ( ! $this->empty)
		{
			// Only append the logical operator between conditions
			$this->open = ' '.strtoupper($logic).' '.$this->open;
		}

		$this->empty = TRUE;
		$this->value .= $this->open;

		if ($left !== NULL OR $operator !== NULL)
		{
			$this->add(NULL, $left, $operator, $right);
		}

		return $this;
	}

	/**
	 * Open a negated parenthesis while converting the LHS to a column.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function not_open_column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Expression
			AND ! $left_column instanceof Identifier)
		{
			$left_column = new Column($left_column);
		}

		return $this->not_open($logic, $left_column, $operator, $right);
	}

	/**
	 * Open a negated parenthesis while converting both operands to columns.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function not_open_columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Expression
			AND ! $right_column instanceof Identifier)
		{
			$right_column = new Column($right_column);
		}

		return $this->not_open_column($logic, $left_column, $operator, $right_column);
	}

	/**
	 * Open a parenthesis using a logical operator when necessary, optionally
	 * adding another condition.
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function open($logic, $left = NULL, $operator = NULL, $right = NULL)
	{
		$this->open = '(';

		if ( ! $this->empty)
		{
			// Only append the logical operator between conditions
			$this->open = ' '.strtoupper($logic).' '.$this->open;
		}

		$this->empty = TRUE;
		$this->value .= $this->open;

		if ($left !== NULL OR $operator !== NULL)
		{
			$this->add(NULL, $left, $operator, $right);
		}

		return $this;
	}

	/**
	 * Open a parenthesis while converting the LHS to a column.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function open_column($logic, $left_column, $operator, $right)
	{
		if ( ! $left_column instanceof Expression
			AND ! $left_column instanceof Identifier)
		{
			$left_column = new Column($left_column);
		}

		return $this->open($logic, $left_column, $operator, $right);
	}

	/**
	 * Open a parenthesis while converting both operands to columns.
	 *
	 * @param   string                              $logic          Logical operator
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function open_columns($logic, $left_column, $operator, $right_column)
	{
		if ( ! $right_column instanceof Expression
			AND ! $right_column instanceof Identifier)
		{
			$right_column = new Column($right_column);
		}

		return $this->open_column($logic, $left_column, $operator, $right_column);
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
	 * Close parenthesis or remove the previous open parenthesis when the
	 * subexpression is empty.
	 *
	 * @return  $this
	 */
	public function close_empty()
	{
		if ( ! $this->empty)
			return $this->close();

		// Remove the previous open parenthesis
		$this->value = substr($this->value, 0, - strlen($this->open));

		$this->empty = (
			// The expression is empty or a subexpression has just begun
			! $this->value OR substr_compare($this->value, '(', -1) === 0
		);

		return $this;
	}


	// Helpers

	/**
	 * Add a condition using AND while converting the LHS to a column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function and_column($left_column, $operator, $right)
	{
		return $this->column('AND', $left_column, $operator, $right);
	}

	/**
	 * Add a condition using AND while converting both operands to columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function and_columns($left_column, $operator, $right_column)
	{
		return $this->columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * Add an EXISTS condition using AND.
	 *
	 * @param   mixed   $query  Converted to Expression
	 * @return  $this
	 */
	public function and_exists($query)
	{
		return $this->exists('AND', $query);
	}

	/**
	 * Add a negated condition using AND.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function and_not($left, $operator = NULL, $right = NULL)
	{
		return $this->not('AND', $left, $operator, $right);
	}

	/**
	 * Add a negated condition using AND while converting the LHS to a column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function and_not_column($left_column, $operator, $right)
	{
		return $this->not_column('AND', $left_column, $operator, $right);
	}

	/**
	 * Add a negated condition using AND while converting both operands to
	 * columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function and_not_columns($left_column, $operator, $right_column)
	{
		return $this->not_columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * Add a NOT EXISTS condition using AND.
	 *
	 * @param   mixed   $query  Converted to Expression
	 * @return  $this
	 */
	public function and_not_exists($query)
	{
		return $this->not_exists('AND', $query);
	}

	/**
	 * Open a negated parenthesis using AND, optionally adding another
	 * condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function and_not_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->not_open('AND', $left, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using AND while converting the LHS to a
	 * column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function and_not_open_column($left_column, $operator, $right)
	{
		return $this->not_open_column('AND', $left_column, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using AND while converting both operands to
	 * columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function and_not_open_columns($left_column, $operator, $right_column)
	{
		return $this->not_open_columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * Open a parenthesis using AND, optionally adding another condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function and_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->open('AND', $left, $operator, $right);
	}

	/**
	 * Open a parenthesis using AND while converting the LHS to a column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function and_open_column($left_column, $operator, $right)
	{
		return $this->open_column('AND', $left_column, $operator, $right);
	}

	/**
	 * Open a parenthesis using AND while converting both operands to columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function and_open_columns($left_column, $operator, $right_column)
	{
		return $this->open_columns('AND', $left_column, $operator, $right_column);
	}

	/**
	 * Add a condition using OR while converting the LHS to a column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function or_column($left_column, $operator, $right)
	{
		return $this->column('OR', $left_column, $operator, $right);
	}

	/**
	 * Add a condition using OR while converting both operands to columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function or_columns($left_column, $operator, $right_column)
	{
		return $this->columns('OR', $left_column, $operator, $right_column);
	}

	/**
	 * Add an EXISTS condition using OR.
	 *
	 * @param   mixed   $query  Converted to Expression
	 * @return  $this
	 */
	public function or_exists($query)
	{
		return $this->exists('OR', $query);
	}

	/**
	 * Add a negated condition using OR.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function or_not($left, $operator = NULL, $right = NULL)
	{
		return $this->not('OR', $left, $operator, $right);
	}

	/**
	 * Add a negated condition using OR while converting the LHS to a column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function or_not_column($left_column, $operator, $right)
	{
		return $this->not_column('OR', $left_column, $operator, $right);
	}

	/**
	 * Add a negated condition using OR while converting both operands to
	 * columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function or_not_columns($left_column, $operator, $right_column)
	{
		return $this->not_columns('OR', $left_column, $operator, $right_column);
	}

	/**
	 * Add a NOT EXISTS condition using OR.
	 *
	 * @param   mixed   $query  Converted to Expression
	 * @return  $this
	 */
	public function or_not_exists($query)
	{
		return $this->not_exists('OR', $query);
	}

	/**
	 * Open a negated parenthesis using OR, optionally adding another condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function or_not_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->not_open('OR', $left, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using OR while converting the LHS to a column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function or_not_open_column($left_column, $operator, $right)
	{
		return $this->not_open_column('OR', $left_column, $operator, $right);
	}

	/**
	 * Open a negated parenthesis using OR while converting both operands to
	 * columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function or_not_open_columns($left_column, $operator, $right_column)
	{
		return $this->not_open_columns('OR', $left_column, $operator, $right_column);
	}

	/**
	 * Open a parenthesis using OR, optionally adding another condition.
	 *
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function or_open($left = NULL, $operator = NULL, $right = NULL)
	{
		return $this->open('OR', $left, $operator, $right);
	}

	/**
	 * Open a parenthesis using OR while converting the LHS to a column.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   mixed                               $right          Right operand
	 * @return  $this
	 */
	public function or_open_column($left_column, $operator, $right)
	{
		return $this->open_column('OR', $left_column, $operator, $right);
	}

	/**
	 * Open a parenthesis using OR while converting both operands to columns.
	 *
	 * @param   array|string|Expression|Identifier  $left_column    Left operand, converted to Column
	 * @param   string                              $operator       Comparison operator
	 * @param   array|string|Expression|Identifier  $right_column   Right operand, converted to Column
	 * @return  $this
	 */
	public function or_open_columns($left_column, $operator, $right_column)
	{
		return $this->open_columns('OR', $left_column, $operator, $right_column);
	}
}
