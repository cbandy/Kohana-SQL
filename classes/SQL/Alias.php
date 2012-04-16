<?php
namespace SQL;

/**
 * Expression for appending an alias to any value.
 *
 * @package     SQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Alias extends Expression
{
	/**
	 * @param   mixed                           $value
	 * @param   string|Expression|Identifier    $alias  Converted to Identifier
	 */
	public function __construct($value, $alias)
	{
		if ( ! $alias instanceof Expression AND ! $alias instanceof Identifier)
		{
			$alias = new Identifier($alias);
		}

		parent::__construct('? AS ?', array($value, $alias));
	}
}
