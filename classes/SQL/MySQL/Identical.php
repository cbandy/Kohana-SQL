<?php
namespace SQL\MySQL;

use SQL\Identical as SQL_Identical;

/**
 * Null-safe equality comparator for MySQL.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/comparison-operators.html#operator_equal-to
 */
class Identical extends SQL_Identical
{
	public function __toString()
	{
		return ($this->value === '=')
			? ':left <=> :right'
			: 'NOT (:left <=> :right)';
	}
}
