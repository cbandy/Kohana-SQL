<?php
namespace SQL\PostgreSQL;

use SQL\Identical as SQL_Identical;

/**
 * Null-safe equality comparator for PostgreSQL.
 *
 * @package     SQL
 * @subpackage  PostgreSQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/functions-comparison.html
 */
class Identical extends SQL_Identical
{
	public function __toString()
	{
		return ($this->value === '=')
			? ':left IS NOT DISTINCT FROM :right'
			: ':left IS DISTINCT FROM :right';
	}
}
