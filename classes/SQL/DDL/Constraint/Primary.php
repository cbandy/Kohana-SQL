<?php
namespace SQL\DDL\Constraint;

use SQL\Column;
use SQL\DDL\Constraint;
use SQL\Expression;
use SQL\Identifier;

/**
 * Generic PRIMARY KEY constraint.
 *
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
 * @link http://msdn.microsoft.com/library/ms191236.aspx Transact-SQL
 */
class Primary extends Constraint
{
	/**
	 * @var array   Columns that must contain unique values and no nulls
	 */
	public $columns;

	/**
	 * @uses columns()
	 *
	 * @param   array   $columns    List of columns, each converted to Column
	 */
	public function __construct($columns = NULL)
	{
		parent::__construct();

		$this->columns =& $this->parameters[':columns'];

		$this->columns($columns);
	}

	public function __toString()
	{
		$value = parent::__toString().'PRIMARY KEY';

		if ($this->columns)
		{
			$value .= ' (:columns)';
		}

		return $value;
	}

	/**
	 * Append multiple columns that must contain unique values and no nulls.
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
}
