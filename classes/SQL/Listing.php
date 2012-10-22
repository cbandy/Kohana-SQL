<?php
namespace SQL;

/**
 * Expression for joining a list of values by a separator.
 *
 * [!!] [Compiler::quote] treats an array as a comma-separated list
 *
 * @package     SQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Compiler::quote()
 */
class Listing extends Expression
{
	/**
	 * @var string
	 */
	public $separator;

	/**
	 * @var array
	 */
	public $values;

	/**
	 * @param   string  $separator  Separator
	 * @param   array   $values     List of values
	 */
	public function __construct($separator, $values = array())
	{
		$this->values =& $this->parameters;

		$this->separator = $separator;
		$this->values = $values;
	}

	public function __toString()
	{
		$count = count($this->values);

		return ($count > 0)
			? str_repeat('?'.$this->separator, $count - 1).'?'
			: '';
	}
}
