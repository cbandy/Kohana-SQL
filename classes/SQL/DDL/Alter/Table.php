<?php
namespace SQL\DDL;

use SQL\Column as SQL_Column;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * Generic ALTER TABLE statement. Some drivers do not support some features.
 *
 * @package     SQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/alter-table.html MySQL
 * @link http://www.postgresql.org/docs/current/static/sql-altertable.html PostgreSQL
 * @link http://www.sqlite.org/lang_altertable.html SQLite
 * @link http://msdn.microsoft.com/library/ms190273.aspx Transact-SQL
 */
class Alter_Table extends Expression
{
	/**
	 * @var array   List of changes to make to the table
	 */
	public $actions;

	/**
	 * @var string  Name of the table to be altered
	 */
	public $name;

	/**
	 * @uses name()
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Table
	 */
	public function __construct($name = NULL)
	{
		parent::__construct('ALTER TABLE :name :actions');

		$this->actions  =& $this->parameters[':actions'];
		$this->name     =& $this->parameters[':name'];

		if ($name !== NULL)
		{
			$this->name($name);
		}
	}

	/**
	 * Add a column to the table.
	 *
	 * @param   Column  $column Column definition
	 * @return  $this
	 */
	public function add_column($column)
	{
		$this->actions[] = new Expression('ADD ?', array($column));

		return $this;
	}

	/**
	 * Add a constraint to the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   Constraint  $constraint
	 * @return  $this
	 */
	public function add_constraint($constraint)
	{
		$this->actions[] = new Expression('ADD ?', array($constraint));

		return $this;
	}

	/**
	 * Remove a column from the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Column
	 * @return  $this
	 */
	public function drop_column($name)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->actions[] = new Expression('DROP COLUMN ?', array($name));

		return $this;
	}

	/**
	 * Remove a constraint from the table.
	 *
	 * [!!] Not supported by SQLite
	 *
	 * @param   string                              $type   CHECK, FOREIGN, PRIMARY or UNIQUE
	 * @param   array|string|Expression|Identifier  $name   Converted to Identifier
	 * @return  $this
	 */
	public function drop_constraint($type, $name)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new Identifier($name);
		}

		$this->actions[] = new Expression('DROP CONSTRAINT ?', array($name));

		return $this;
	}

	/**
	 * Remove the default value on a column.
	 *
	 * [!!] Not supported by SQLite nor SQL Server
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Column
	 * @return  $this
	 */
	public function drop_default($name)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->actions[] = new Expression('ALTER ? DROP DEFAULT', array($name));

		return $this;
	}

	/**
	 * Set the name of the table to be altered.
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
	 * Rename the table. This cannot be combined with other actions.
	 *
	 * [!!] Not supported by SQL Server
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Table
	 * @return  $this
	 */
	public function rename($name)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new Table($name);
		}

		$this->actions = array(new Expression('RENAME TO ?', array($name)));

		return $this;
	}

	/**
	 * Set the default value of a column.
	 *
	 * [!!] Not supported by SQLite nor SQL Server
	 *
	 * @param   array|string|Expression|Identifier  $name   Converted to Column
	 * @param   mixed                                       $value
	 * @return  $this
	 */
	public function set_default($name, $value)
	{
		if ( ! $name instanceof Expression AND ! $name instanceof Identifier)
		{
			$name = new SQL_Column($name);
		}

		$this->actions[] = new Expression(
			'ALTER ? SET DEFAULT ?', array($name, $value)
		);

		return $this;
	}
}
