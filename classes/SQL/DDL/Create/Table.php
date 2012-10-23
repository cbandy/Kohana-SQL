<?php
namespace SQL\DDL;

use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * Generic CREATE TABLE statement. Some drivers do not support some features.
 *
 * @package     SQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createtable.html PostgreSQL
 * @link http://www.sqlite.org/lang_createtable.html SQLite
 * @link http://msdn.microsoft.com/library/ms174979.aspx Transact-SQL
 */
class Create_Table extends Expression
{
	/**
	 * @var array   Column definitions
	 */
	public $columns;

	/**
	 * @var array   Table constraints
	 */
	public $constraints;

	/**
	 * @var Table   Name of the table
	 */
	public $name;

	/**
	 * @var Expression  Query from which the table definition is inferred
	 */
	public $query;

	/**
	 * @var boolean Whether or not the table should be dropped at the end of the session
	 */
	public $temporary;

	/**
	 * @uses name()
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Table
	 */
	public function __construct($name = NULL)
	{
		$this->columns      =& $this->parameters[':columns'];
		$this->constraints  =& $this->parameters[':constraints'];
		$this->name         =& $this->parameters[':name'];
		$this->query        =& $this->parameters[':query'];

		if ($name !== NULL)
		{
			$this->name($name);
		}
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->temporary)
		{
			// Not allowed in MSSQL
			$value .= ' TEMPORARY';
		}

		$value .= ' TABLE :name';

		if ($this->query)
		{
			if ($this->columns)
			{
				$value .= ' (:columns)';
			}

			// Not allowed in PostgreSQL
			// Not allowed in MSSQL
			$value .= ' AS (:query)';
		}
		else
		{
			$value .= ' (:columns';

			if ($this->constraints)
			{
				$value .= ', :constraints';
			}

			$value .= ')';
		}

		return $value;
	}

	/**
	 * Append a column definition.
	 *
	 * @param   Column  $column
	 * @return  $this
	 */
	public function column($column)
	{
		$this->columns[] = $column;

		return $this;
	}

	/**
	 * Append multiple column definitions.
	 *
	 * @param   array   $columns    List of Column definitions or NULL to reset
	 * @return  $this
	 */
	public function columns($columns)
	{
		if ($columns === NULL)
		{
			$this->columns = NULL;
		}
		else
		{
			foreach ($columns as $column)
			{
				$this->columns[] = $column;
			}
		}

		return $this;
	}

	/**
	 * Append a table constraint.
	 *
	 * @param   Constraint  $constraint NULL to reset
	 * @return  $this
	 */
	public function constraint($constraint)
	{
		$this->constraints[] = $constraint;

		return $this;
	}

	/**
	 * Append multiple table constraints.
	 *
	 * @param   array   $constraints    List of Constraint or NULL to reset
	 * @return  $this
	 */
	public function constraints($constraints)
	{
		if ($constraints === NULL)
		{
			$this->constraints = NULL;
		}
		else
		{
			foreach ($constraints as $constraint)
			{
				$this->constraints[] = $constraint;
			}
		}

		return $this;
	}

	/**
	 * Set the name of the table.
	 *
	 * @param   array|string|Expression|Identifier  $value  Converted to Table
	 * @return  $this
	 */
	public function name($value)
	{
		if ( ! $value instanceof Expression AND ! $value instanceof Identifier)
		{
			$value = new Table($value);
		}

		$this->name = $value;

		return $this;
	}

	/**
	 * Set the query from which the table definition is inferred.
	 *
	 * [!!] Not supported by PostgreSQL nor SQL Server
	 *
	 * @param   Expression  $query
	 * @return  $this
	 */
	public function query($query)
	{
		$this->query = $query;

		return $this;
	}

	/**
	 * Set whether or not the table should be dropped at the end of the session.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function temporary($value = TRUE)
	{
		$this->temporary = $value;

		return $this;
	}
}
