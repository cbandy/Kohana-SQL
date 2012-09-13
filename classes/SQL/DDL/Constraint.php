<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;

/**
 * @package     SQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/ddl-constraints.html PostgreSQL
 * @link http://www.sqlite.org/syntaxdiagrams.html#table-constraint SQLite
 * @link http://msdn.microsoft.com/library/ms189862.aspx Transact-SQL
 */
abstract class Constraint extends Expression
{
	/**
	 * @var Identifier  Name of the constraint
	 */
	public $name;

	public function __construct()
	{
		$this->name =& $this->parameters[':name'];
	}

	public function __toString()
	{
		return $this->name ? 'CONSTRAINT :name ' : '';
	}

	/**
	 * Set the name of the constraint.
	 *
	 * @param   array|string|Expression|Identifier  $value  Converted to Identifier
	 * @return  $this
	 */
	public function name($value)
	{
		if ($value !== NULL
			AND ! $value instanceof Expression
			AND ! $value instanceof Identifier)
		{
			$value = new Identifier($value);
		}

		$this->name = $value;

		return $this;
	}
}
