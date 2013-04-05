<?php
namespace SQL\Literal;

use SQL\Literal;

/**
 * Value that should be treated as a binary literal when an [SQL\Expression] is
 * parsed or quoted.
 *
 * @package     SQL
 * @category    Literals
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Compiler::quote_binary()
 * @see Compiler::quote_literal()
 */
class Binary extends Literal
{
}
