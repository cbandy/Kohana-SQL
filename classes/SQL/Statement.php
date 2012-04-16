<?php
namespace SQL;

/**
 * A natively parameterized SQL statement. Placeholders are driver-specific and
 * all parameters are unquoted literals.
 *
 * This is not a prepared statement.
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Statement
{
	/**
	 * @var array   Unquoted literal parameters
	 */
	protected $parameters;

	/**
	 * @var string  SQL statement
	 */
	protected $statement;

	/**
	 * @param   string  $statement  SQL statement
	 * @param   array   $parameters Unquoted literal parameters
	 */
	public function __construct($statement, $parameters = array())
	{
		$this->statement = $statement;
		$this->parameters = $parameters;
	}

	public function __toString()
	{
		return $this->statement;
	}

	/**
	 * Bind a variable to a parameter. Parameter names are driver-specific.
	 *
	 * @param   integer|string  $parameter  Parameter index or name
	 * @param   mixed           $var        Variable to bind
	 * @return  $this
	 */
	public function bind($parameter, &$var)
	{
		$this->parameters[$parameter] =& $var;

		return $this;
	}

	/**
	 * Set the value of a parameter. Parameter names are driver-specific.
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
	 * Return the current parameter values. Parameter names are driver-specific.
	 *
	 * @return  array
	 */
	public function parameters()
	{
		return $this->parameters;
	}
}
