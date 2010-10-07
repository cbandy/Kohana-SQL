<?php

/**
 * @package     RealDatabase
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_Identifier
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
