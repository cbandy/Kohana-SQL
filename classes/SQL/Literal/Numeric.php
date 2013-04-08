<?php
namespace SQL\Literal;

use SQL\Literal;

/**
 * Value that should be treated as numeric literal when an [Expression] is
 * parsed or quoted.
 *
 * @package     SQL
 * @category    Literals
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Compiler::quote_literal()
 * @see Compiler::quote_numeric()
 */
class Numeric extends Literal
{
	/**
	 * @var integer Number of digits in the fractional part
	 */
	public $scale;

	/**
	 * @param   mixed   $value
	 * @param   integer $scale  Number of digits in the fractional part
	 */
	public function __construct($value, $scale)
	{
		parent::__construct($value);

		$this->scale = $scale;
	}
}
