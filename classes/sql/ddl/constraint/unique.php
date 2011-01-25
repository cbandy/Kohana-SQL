<?php

/**
 * @package     RealDatabase
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/ddl-constraints.html PostgreSQL
 * @link http://www.sqlite.org/syntaxdiagrams.html#table-constraint SQLite
 * @link http://msdn.microsoft.com/en-us/library/ms191166.aspx Transact-SQL
 */
class SQL_DDL_Constraint_Unique extends SQL_DDL_Constraint
{
	/**
	 * @uses SQL_DDL_Constraint_Unique::columns()
	 *
	 * @param   array   $columns    Each element converted to SQL_Column
	 */
	public function __construct($columns = array())
	{
		parent::__construct('UNIQUE');

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = parent::__toString().$this->_value;

		if ( ! empty($this->parameters[':columns']))
		{
			$value .= ' (:columns)';
		}

		return $value;
	}

	/**
	 * Set the group of columns that must contain unique values
	 *
	 * @param   array   $columns    Each element converted to SQL_Column
	 * @return  $this
	 */
	public function columns($columns)
	{
		foreach ($columns as & $column)
		{
			if ( ! $column instanceof SQL_Expression
				AND ! $column instanceof SQL_Identifier)
			{
				$column = new SQL_Column($column);
			}
		}

		$this->parameters[':columns'] = $columns;

		return $this;
	}
}