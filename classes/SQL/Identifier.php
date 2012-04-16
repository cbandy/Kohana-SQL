<?php
namespace SQL;

/**
 * The name of an object in the database, such as a column, constraint, index
 * or table.
 *
 * Use the more specific [Table] and [Column] for tables and columns,
 * respectively.
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @see Compiler::quote_identifier()
 */
class Identifier
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array|string|Identifier
	 */
	public $namespace;

	/**
	 * @param   array|string    $value
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
