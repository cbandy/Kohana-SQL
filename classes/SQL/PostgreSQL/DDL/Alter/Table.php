<?php
namespace SQL\PostgreSQL\DDL;

use SQL\Column as SQL_Column;
use SQL\DDL\Alter_Table as SQL_Alter_Table;
use SQL\Expression;
use SQL\Identifier;

/**
 * ALTER TABLE statement for PostgreSQL. Allows the name and type of columns to
 * be changed.
 *
 * @package     SQL
 * @subpackage  PostgreSQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/sql-altertable.html
 */
class Alter_Table extends SQL_Alter_Table
{
	/**
	 * Remove a column from the table, optionally removing dependent objects.
	 *
	 * @param   array|string|Expression|Identifier  $name       Converted to SQL\Column
	 * @param   boolean                             $cascade    Whether or not dependent objects should be dropped
	 * @return  $this
	 */
	public function drop_column($name, $cascade = NULL)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new SQL_Column($name);
		}

		$result = new Expression('DROP COLUMN ?', array($name));

		if ($cascade !== NULL)
		{
			$result->value .= $cascade ? ' CASCADE' : ' RESTRICT';
		}

		$this->actions[] = $result;

		return $this;
	}

	/**
	 * Remove a constraint from the table, optionally removing dependent
	 * objects.
	 *
	 * @param   string                              $type       Ignored
	 * @param   array|string|Expression|Identifier  $name       Converted to Identifier
	 * @param   boolean                             $cascade    Whether or not dependent objects should be dropped
	 * @return  $this
	 */
	public function drop_constraint($type, $name, $cascade = NULL)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new Identifier($name);
		}

		$result = new Expression('DROP CONSTRAINT ?', array($name));

		if ($cascade !== NULL)
		{
			$result->value .= $cascade ? ' CASCADE' : ' RESTRICT';
		}

		$this->actions[] = $result;

		return $this;
	}

	/**
	 * Rename a column.
	 *
	 * [!!] This cannot be combined with other actions.
	 *
	 * @param   array|string|Expression|Identifier  $old_name   Converted to SQL\Column
	 * @param   array|string|Expression|Identifier  $new_name   Converted to SQL\Column
	 * @return  $this
	 */
	public function rename_column($old_name, $new_name)
	{
		if ( ! $old_name instanceof Expression
			AND ! $old_name instanceof Identifier)
		{
			$old_name = new SQL_Column($old_name);
		}

		if ( ! $new_name instanceof Expression
			AND ! $new_name instanceof Identifier)
		{
			$new_name = new SQL_Column($new_name);
		}

		$this->actions = array(
			new Expression('RENAME ? TO ?', array($old_name, $new_name))
		);

		return $this;
	}

	/**
	 * Add or remove the NOT NULL constraint on a column.
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to SQL\Column
	 * @param   boolean                             $value  TRUE to add or FALSE to remove
	 * @return  $this
	 */
	public function set_not_null($name, $value = TRUE)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->actions[] = new Expression(
			($value ? 'SET' : 'DROP').' NOT NULL ?', array($name)
		);

		return $this;
	}

	/**
	 * Change the type of a column, optionally using an expression to facilitate
	 * the conversion.
	 *
	 * @param   array|string|Expression|Identifier  $column Converted to SQL\Column
	 * @param   mixed                               $type   Converted to Expression
	 * @param   mixed                               $using  Converted to Expression
	 * @return  $this
	 */
	public function type($name, $type, $using = NULL)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new SQL_Column($name);
		}

		if ( ! $type instanceof Expression)
		{
			$type = new Expression($type);
		}

		$result = new Expression('ALTER ? TYPE ?', array($name, $type));

		if ($using !== NULL)
		{
			if ( ! $using instanceof Expression)
			{
				$using = new Expression($using);
			}

			$result->value .= ' USING ?';
			$result->parameters[] = $using;
		}

		$this->actions[] = $result;

		return $this;
	}
}
