<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;

/**
 * Generic DROP statement. Some drivers do not support some features. Use the
 * more specific [Drop_Table] statement for dropping tables.
 *
 * @package     SQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/sql-syntax-data-definition.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-commands.html PostgreSQL
 * @link http://www.sqlite.org/lang.html SQLite
 * @link http://msdn.microsoft.com/library/cc879259.aspx Transact-SQL
 */
class Drop extends Expression
{
	/**
	 * @var boolean Whether or not dependent objects should be dropped
	 */
	public $cascade;

	/**
	 * @var boolean Whether or not an error should be suppressed if the object does not exist
	 */
	public $if_exists;

	/**
	 * @var array   List of objects to drop
	 */
	public $names;

	/**
	 * @var string  Type of objects to drop
	 */
	public $type;

	/**
	 * @uses name()
	 * @uses cascade()
	 *
	 * @param   string                              $type       INDEX, SCHEMA, VIEW, etc.
	 * @param   array|string|Expression|Identifier  $name       Converted to Identifier
	 * @param   boolean                             $cascade    Whether or not dependent objects should be dropped
	 */
	public function __construct($type, $name = NULL, $cascade = NULL)
	{
		$this->names =& $this->parameters[':names'];
		$this->type = $type;

		$this->cascade($cascade);

		if ($name !== NULL)
		{
			$this->name($name);
		}
	}

	public function __toString()
	{
		$value = 'DROP '.strtoupper($this->type);

		if ($this->if_exists)
		{
			// Not allowed in MSSQL
			$value .= ' IF EXISTS';
		}

		$value .= ' :names';

		if ($this->cascade !== NULL)
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			// Not allowed in SQLite
			$value .= $this->cascade ? ' CASCADE' : ' RESTRICT';
		}

		return $value;
	}

	/**
	 * Set whether or not dependent objects should be dropped.
	 *
	 * [!!] Not supported by MySQL, SQLite nor SQL Server
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function cascade($value = TRUE)
	{
		$this->cascade = $value;

		return $this;
	}

	/**
	 * Set whether or not an error should be suppressed if the object does not
	 * exist.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function if_exists($value = TRUE)
	{
		$this->if_exists = $value;

		return $this;
	}

	/**
	 * Append the name of an object to be dropped.
	 *
	 * [!!] SQLite allows only one object per statement
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Identifier
	 * @return  $this
	 */
	public function name($name)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new Identifier($name);
		}

		$this->names[] = $name;

		return $this;
	}

	/**
	 * Append the names of multiple objects to be dropped.
	 *
	 * [!!] SQLite allows only one object per statement
	 *
	 * @param   array   $names  List of names, each converted to Identifier, or NULL to reset
	 * @return  $this
	 */
	public function names($names)
	{
		if ($names === NULL)
		{
			$this->names = NULL;
		}
		else
		{
			foreach ($names as $name)
			{
				$this->name($name);
			}
		}

		return $this;
	}
}
