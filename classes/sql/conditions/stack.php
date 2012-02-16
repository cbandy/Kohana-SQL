<?php

/**
 * Expression for building and combining a stack of predicates.
 *
 * @package     RealDatabase
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class SQL_Conditions_Stack extends SQL_Conditions
{
	/**
	 * @var array   List of tuples containing the expression length, number of
	 *     parameters and, optionally, the content of an open() operation
	 */
	protected $_values = array();

	/**
	 * Push the current expression state onto the stack.
	 *
	 * @param   string  $open   Content of an open() operation that should be
	 *     restored during pop()
	 * @return  void
	 */
	protected function _push($open)
	{
		$this->_values[] = array(
			strlen($this->_value),
			count($this->parameters),
			$open,
		);
	}

	public function add($logic, $left, $operator = NULL, $right = NULL)
	{
		$this->_push(NULL);

		return parent::add($logic, $left, $operator, $right);
	}

	public function exists($logic, $query)
	{
		$this->_push(NULL);

		return parent::exists($logic, $query);
	}

	public function not($logic, $left, $operator = NULL, $right = NULL)
	{
		$this->_push(NULL);

		return parent::not($logic, $left, $operator, $right);
	}

	public function not_exists($logic, $query)
	{
		$this->_push(NULL);

		return parent::not_exists($logic, $query);
	}

	/**
	 * Open a negated parenthesis using a logical operator when necessary,
	 * optionally adding another condition.
	 *
	 * [!!] When adding another condition this way, two pops() are needed to
	 *      remove the open parenthesis
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function not_open($logic, $left = NULL, $operator = NULL, $right = NULL)
	{
		$this->_push(NULL);

		return parent::not_open($logic, $left, $operator, $right);
	}

	/**
	 * Open a parenthesis using a logical operator when necessary, optionally
	 * adding another condition.
	 *
	 * [!!] When adding another condition this way, two pops() are needed to
	 *      remove the open parenthesis
	 *
	 * @param   string  $logic      Logical operator
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Comparison operator
	 * @param   mixed   $right      Right operand
	 * @return  $this
	 */
	public function open($logic, $left = NULL, $operator = NULL, $right = NULL)
	{
		$this->_push(NULL);

		return parent::open($logic, $left, $operator, $right);
	}

	public function close()
	{
		$this->_push(NULL);

		return parent::close();
	}

	public function close_empty()
	{
		$this->_push($this->_empty ? $this->_open : NULL);

		return parent::close_empty();
	}

	/**
	 * Remove one operation from the stack of predicates.
	 *
	 * @return  $this
	 */
	public function pop()
	{
		list($value, $parameters, $open) = array_pop($this->_values);

		$this->_value = substr($this->_value, 0, $value).$open;

		while (count($this->parameters) > $parameters)
		{
			array_pop($this->parameters);
		}

		return $this;
	}
}
