<?php
namespace SQL\Result;

/**
 * @package     SQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
interface Types
{
	/**
	 * Return the type of a column.
	 *
	 * @param   string  $name   Column name or NULL to return the first
	 * @return  string
	 */
	public function type($name = NULL);
}
