<?php
namespace SQL\MySQL\DDL;

use SQL\Column as SQL_Column;
use SQL\DDL\Alter_Table as SQL_Alter_Table;
use SQL\Expression;
use SQL\Identifier;

/**
 * ALTER TABLE statement for MySQL. Allows the name, type and position of
 * columns to be changed.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/alter-table.html
 */
class Alter_Table extends SQL_Alter_Table
{
	/**
	 * Add a column to the table, optionally specifying the position.
	 *
	 * @param   SQL\DDL\Column                              $column
	 * @param   array|boolean|string|Expression|Identifier  $after  TRUE for
	 *  FIRST or Converted to SQL\Column
	 * @return  $this
	 */
	public function add_column($column, $after = FALSE)
	{
		$this->actions[] = $this->position(
			new Expression('ADD ?', array($column)), $after
		);

		return $this;
	}

	/**
	 * Change a column in the table, optionally specifying the position.
	 *
	 * @param   array|string|Expression|Identifier          $name   Converted to
	 *  SQL\Column
	 * @param   SQL\DDL\Column                              $column
	 * @param   array|boolean|string|Expression|Identifier  $after  TRUE for
	 *  FIRST or Converted to SQL\Column
	 * @return  $this
	 */
	public function change_column($name, $column, $after = FALSE)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->actions[] = $this->position(
			new Expression('CHANGE ? ?', array($name, $column)), $after
		);

		return $this;
	}

	public function drop_constraint($type, $name)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new Identifier($name);
		}

		$type = strtoupper($type);

		if ($type === 'FOREIGN')
		{
			$this->actions[] = new Expression(
				'DROP FOREIGN KEY ?', array($name)
			);
		}
		elseif ($type === 'PRIMARY')
		{
			$this->actions[] = new Expression('DROP PRIMARY KEY');
		}
		elseif ($type !== 'CHECK')
		{
			$this->actions[] = new Expression('DROP INDEX ?', array($name));
		}

		return $this;
	}

	/**
	 * Set multiple table options.
	 *
	 * @param   array|Options   $options    Hash of (option => value) pairs
	 * @return  $this
	 */
	public function options($options)
	{
		if (is_array($options))
		{
			$options = new Options($options);
		}

		$this->actions[] = $options;

		return $this;
	}

	/**
	 * Append a FIRST or AFTER clause to an Expression.
	 *
	 * @param   Expression                                  $expression
	 * @param   array|boolean|string|Expression|Identifier  $after      TRUE for
	 *  FIRST or Converted to SQL\Column
	 * @return  Expression Modified expression object
	 */
	protected function position($expression, $after)
	{
		if ($after === TRUE)
		{
			$expression->value .= ' FIRST';
		}
		elseif ($after)
		{
			if ( ! $after instanceof Expression
				AND ! $after instanceof Identifier)
			{
				$after = new SQL_Column($after);
			}

			$expression->value .= ' AFTER ?';
			$expression->parameters[] = $after;
		}

		return $expression;
	}
}
