<?php
namespace SQL;

/**
 * A SQL fragment that may contain placeholders. When the expression is
 * processed for execution, placeholders are replaced with their escaped/quoted
 * parameter values.
 *
 * Positional placeholders are indicated by a `?` while named placeholders begin
 * with a colon.
 *
 * Anything may be used as a parameter value including other [Expression]s.
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Compiler::quote_expression()
 */
class Expression
{
	/**
	 * @var mixed   SQL expression with or without parameter placeholders
	 */
	protected $value;

	/**
	 * @var array   Unquoted parameters
	 */
	public $parameters;

	/**
	 * @param   mixed   $value      SQL expression
	 * @param   array   $parameters Unquoted parameters
	 */
	public function __construct($value, $parameters = array())
	{
		$this->value = $value;
		$this->parameters = $parameters;
	}

	public function __toString()
	{
		return (string) $this->value;
	}

	/**
	 * Bind a variable to a parameter. Names must begin with colon.
	 *
	 * @param   integer|string  $parameter  Parameter index or name
	 * @param   mixed           $variable   Variable to bind
	 * @return  $this
	 */
	public function bind($parameter, &$variable)
	{
		$this->parameters[$parameter] =& $variable;

		return $this;
	}

	/**
	 * Set the value of a parameter. Names must begin with colon.
	 *
	 * @param   integer|string  $parameter  Parameter index or name
	 * @param   mixed           $value      Value to assign
	 * @return  $this
	 */
	public function param($parameter, $value)
	{
		$this->parameters[$parameter] = $value;

		return $this;
	}

	/**
	 * Add multiple parameter values. Names must begin with colon.
	 *
	 * @param   array   $parameters Values to assign
	 * @return  $this
	 */
	public function parameters($parameters)
	{
		$this->parameters = $parameters + $this->parameters;

		return $this;
	}
}
