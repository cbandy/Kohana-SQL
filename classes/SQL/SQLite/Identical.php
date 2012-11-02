<?php
namespace SQL\SQLite;

use SQL\Identical as SQL_Identical;

/**
 * Null-safe equality comparator for SQLite.
 *
 * @package     SQL
 * @subpackage  SQLite
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_expr.html#binaryops
 */
class Identical extends SQL_Identical
{
	public function __toString()
	{
		return ($this->value === '=')
			? ':left IS :right'
			: ':left IS NOT :right';
	}
}
