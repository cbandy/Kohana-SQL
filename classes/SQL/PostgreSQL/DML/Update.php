<?php
namespace SQL\PostgreSQL\DML;

use SQL\Alias;
use SQL\Column;
use SQL\DML\Update as SQL_Update;
use SQL\Expression;
use SQL\Identifier;

/**
 * UPDATE statement for PostgreSQL. Allows data to be returned from the affected
 * rows.
 *
 * @package     SQL
 * @subpacakge  PostgreSQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-update.html
 */
class Update extends SQL_Update
{
	/**
	 * @var array   Columns, Expressions and/or values to be returned
	 */
	public $returning;

	public function __construct($table = NULL, $alias = NULL, $values = NULL)
	{
		parent::__construct($table, $alias, $values);

		$this->returning =& $this->parameters[':returning'];
	}

	public function __toString()
	{
		$value = parent::__toString();

		if ($this->returning)
		{
			$value .= ' RETURNING :returning';
		}

		return $value;
	}

	/**
	 * Append multiple columns and/or expressions to be returned when executed.
	 *
	 * @param   array   $columns    Hash of (alias => column) pairs or NULL to reset
	 * @return  $this
	 */
	public function returning($columns)
	{
		if ($columns === NULL)
		{
			$this->returning = NULL;
		}
		else
		{
			foreach ($columns as $alias => $column)
			{
				if ( ! $column instanceof Expression
					AND ! $column instanceof Identifier)
				{
					$column = new Column($column);
				}

				if (is_string($alias) AND $alias !== '')
				{
					$column = new Alias($column, $alias);
				}

				$this->returning[] = $column;
			}
		}

		return $this;
	}
}
