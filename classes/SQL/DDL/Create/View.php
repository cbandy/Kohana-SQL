<?php
namespace SQL\DDL;

use SQL\Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * Generic CREATE VIEW statement. Some drivers do not support some features.
 *
 * @package     SQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-view.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-createview.html PostgreSQL
 * @link http://www.sqlite.org/lang_createview.html SQLite
 * @link http://msdn.microsoft.com/library/ms187956.aspx Transact-SQL
 */
class Create_View extends Expression
{
	/**
	 * @var array   Columns or expressions to be included in the view
	 */
	public $columns;

	/**
	 * @var Table   Name of the view
	 */
	public $name;

	/**
	 * @var Expression  Query which will provide the columns and rows
	 */
	public $query;

	/**
	 * @var boolean Whether or not an existing view should be replaced
	 */
	public $replace;

	/**
	 * @var boolean Whether or not the view should be dropped at the end of the session
	 */
	public $temporary;

	/**
	 * @uses name()
	 * @uses query()
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Table
	 * @param   Expression                          $query
	 */
	public function __construct($name = NULL, $query = NULL)
	{
		$this->columns  =& $this->parameters[':columns'];
		$this->name     =& $this->parameters[':name'];
		$this->query    =& $this->parameters[':query'];

		if ($name !== NULL)
		{
			$this->name($name);
		}

		$this->query($query);
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->replace)
		{
			// Not allowed in MSSQL
			// Not allowed in SQLite
			$value .= ' OR REPLACE';
		}

		if ($this->temporary)
		{
			// Not allowed in MSSQL
			// Not allowed in MySQL
			$value .= ' TEMPORARY';
		}

		$value .= ' VIEW :name';

		if ($this->columns)
		{
			// Not allowed in SQLite
			$value .= ' (:columns)';
		}

		$value .= ' AS :query';

		return $value;
	}

	/**
	 * Append one column or expression to be included in the view.
	 *
	 * @param   array|string|Expression|Identifier  $column Converted to Column
	 * @return  $this
	 */
	public function column($column)
	{
		if ( ! $column instanceof Expression
			AND ! $column instanceof Identifier)
		{
			$column = new Column($column);
		}

		$this->columns[] = $column;

		return $this;
	}

	/**
	 * Append multiple columns and/or expressions to be included in the view.
	 *
	 * @param   array   $columns    List of columns, each converted to Column, or NULL to reset
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
				if ( ! $column instanceof Expression
					AND ! $column instanceof Identifier)
				{
					$column = new Column($column);
				}

				$this->columns[] = $column;
			}
		}

		return $this;
	}

	/**
	 * Set the name of the view.
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
	 * Set the query which will provide the columns and rows of the view.
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
	 * Set whether or not an existing view should be replaced.
	 *
	 * [!!] Not supported by SQLite nor SQL Server
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function replace($value = TRUE)
	{
		$this->replace = $value;

		return $this;
	}

	/**
	 * Set whether or not the view should be dropped at the end of the session.
	 *
	 * [!!] Not supported by MySQL nor SQL Server
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
