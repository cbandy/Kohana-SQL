<?php

/**
 * The name of an object in the database, such as a column, constraint, index
 * or table.
 *
 * Use the more specific [SQL_Table] and [SQL_Column] for tables and columns,
 * respectively.
 *
 * @package     RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see SQL::quote_identifier()
 */
class SQL_Identifier
{
	public $name;
	public $namespace;

	/**
	 * @param   array|string
	 */
	public function __construct($value)
	{
		if ( ! is_array($value))
		{
			$value = explode('.', $value);
		}

		$this->name = array_pop($value);
		$this->namespace = $value;
	}
}
