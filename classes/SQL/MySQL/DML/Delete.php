<?php
namespace SQL\MySQL\DML;

use SQL\Column;
use SQL\DML\Delete as SQL_Delete;
use SQL\Expression;
use SQL\Identifier;

/**
 * DELETE statement for MySQL. Allows rows to be deleted according to ORDER BY.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/delete.html
 */
class Delete extends SQL_Delete
{
	/**
	 * @var array   Columns and/or Expressions specifying the order in which
	 *  rows should be deleted
	 */
	public $order_by;

	public function __construct($table = NULL, $alias = NULL)
	{
		parent::__construct($table, $alias);

		$this->order_by =& $this->parameters[':orderby'];
	}

	public function __toString()
	{
		$value = 'DELETE FROM :table';

		if ($this->using)
		{
			$value .= ' USING :using';
		}

		if ($this->where)
		{
			$value .= ' WHERE :where';
		}

		if ($this->order_by)
		{
			$value .= ' ORDER BY :orderby';
		}

		if ($this->limit !== NULL)
		{
			$value .= ' LIMIT :limit';
		}

		return $value;
	}

	/**
	 * Append a column or expression specifying the order in which rows should
	 * be deleted.
	 *
	 * @param   array|string|Expression|Identifier  $column     Converted to Column or NULL to reset
	 * @param   string|Expression                   $direction  Direction of sort
	 * @return  $this
	 */
	public function order_by($column, $direction = NULL)
	{
		if ($column === NULL)
		{
			$this->order_by = NULL;
		}
		else
		{
			if ( ! $column instanceof Expression
				AND ! $column instanceof Identifier)
			{
				$column = new Column($column);
			}

			if ($direction)
			{
				$column = ($direction instanceof Expression)
					? new Expression('? ?', array($column, $direction))
					: new Expression('? '.strtoupper($direction), array($column));
			}

			$this->order_by[] = $column;
		}

		return $this;
	}
}
