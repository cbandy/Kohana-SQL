<?php
namespace SQL;

/**
 * Value that should be treated as literal when an [Expression] is parsed or
 * quoted.
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Compiler::parse_statement()
 * @see Compiler::quote_literal()
 */
class Literal
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
