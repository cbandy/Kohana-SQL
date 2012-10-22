<?php
namespace SQL\DDL;

use SQL\Column as SQL_Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Listing;

/**
 * Generic column expression for use in ALTER TABLE and CREATE TABLE statements.
 *
 * @package     SQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createtable.html PostgreSQL
 * @link http://www.sqlite.org/syntaxdiagrams.html#column-def SQLite
 * @link http://msdn.microsoft.com/library/ms174979.aspx Transact-SQL
 *
 * @property    mixed   $default        Default value of the column
 */
class Column extends Expression
{
	/**
	 * @var Listing Constraints of the column
	 */
	public $constraints;

	/**
	 * @var mixed   Default value of the column
	 */
	protected $default;

	/**
	 * @var boolean Whether or not the column has a default value
	 */
	public $has_default;

	/**
	 * @var SQL\Column  Name of the column
	 */
	public $name;

	/**
	 * @var boolean Whether or not NULL values are prohibited in the column
	 */
	public $not_null;

	/**
	 * @var Expression  Datatype of the column
	 */
	public $type;

	/**
	 * @uses name()
	 * @uses type()
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to SQL\Column
	 * @param   mixed                               $type   Converted to Expression
	 */
	public function __construct($name = NULL, $type = NULL)
	{
		$this->constraints  =& $this->parameters[':constraints'];
		$this->default      =& $this->parameters[':default'];
		$this->name         =& $this->parameters[':name'];
		$this->type         =& $this->parameters[':type'];

		if ($name !== NULL)
		{
			$this->name($name);
		}

		if ($type !== NULL)
		{
			$this->type($type);
		}
	}

	public function __get($name)
	{
		if ($name === 'default')
			return $this->default;
	}

	public function __set($name, $value)
	{
		if ($name === 'default')
		{
			$this->default = $value;
			$this->has_default = TRUE;
		}
	}

	public function __toString()
	{
		$value = ':name :type';

		if ($this->has_default)
		{
			$value .= ' DEFAULT :default';
		}

		if ($this->not_null)
		{
			$value .= ' NOT NULL';
		}

		if ($this->constraints)
		{
			$value .= ' :constraints';
		}

		return $value;
	}

	/**
	 * Set the name of the column.
	 *
	 * @param   array|string|Expression|Identifier  $value  Converted to SQL\Column
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof Expression AND ! $value instanceof Identifier)
		{
			$value = new SQL_Column($value);
		}

		$this->name = $value;

		return $this;
	}

	/**
	 * Unset the default value of the column.
	 *
	 * @return  $this
	 */
	public function no_default()
	{
		$this->default = NULL;
		$this->has_default = FALSE;

		return $this;
	}

	/**
	 * Set whether or not NULL values are prohibited in the column.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function not_null($value = TRUE)
	{
		$this->not_null = $value;

		return $this;
	}

	/**
	 * Set the default value of the column.
	 *
	 * @param   mixed   $value
	 * @return  $this
	 */
	public function set_default($value)
	{
		$this->__set('default', $value);

		return $this;
	}

	/**
	 * Set the datatype of the column.
	 *
	 * @param   mixed   $type   Converted to Expression
	 * @return  $this
	 */
	public function type($type)
	{
		if ( ! $type instanceof Expression)
		{
			$type = new Expression($type);
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * Set the constraints of the column.
	 *
	 * @param   array|Constraint    $constraints    Constraint or list of Constraints
	 * @return  $this
	 */
	public function constraints($constraints)
	{
		if (is_array($constraints))
		{
			$constraints = new Listing(' ', $constraints);
		}

		$this->constraints = $constraints;

		return $this;
	}
}
