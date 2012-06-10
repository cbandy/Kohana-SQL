<?php
namespace SQL;

/**
 * Value that should not be parameterized when an [Expression] is parsed.
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Compiler::parse_statement()
 */
class Constant
{
	/**
	 * @var mixed
	 */
	public $value;

	/**
	 * @param   mixed   $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}
}
