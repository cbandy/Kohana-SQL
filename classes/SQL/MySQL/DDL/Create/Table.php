<?php
namespace SQL\MySQL\DDL;

use SQL\DDL\Create_Table as SQL_Create_Table;
use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * CREATE TABLE statement for MySQL.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html
 */
class Create_Table extends SQL_Create_Table
{
	public function __construct($name = NULL)
	{
		parent::__construct($name);

		$this->like     =& $this->parameters[':like'];
		$this->options  =& $this->parameters[':options'];
	}

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->temporary)
		{
			$value .= ' TEMPORARY';
		}

		$value .= ' TABLE';

		if ($this->if_not_exists)
		{
			$value .= ' IF NOT EXISTS';
		}

		$value .= ' :name';

		if ($this->like)
		{
			$value .= ' LIKE :like';
		}
		else
		{
			if ($this->columns)
			{
				$value .= ' (:columns';

				if ($this->constraints)
				{
					$value .= ', :constraints';
				}

				$value .= ')';
			}

			if ($this->options)
			{
				$value .= ' :options';
			}

			if ($this->query)
			{
				$value .= ' AS :query';
			}
		}

		return $value;
	}

	/**
	 * Set the table from which to copy this table definition.
	 *
	 * @param   array|string|Expression|Identifier  $table  Converted to Table
	 * @return  $this
	 */
	public function like($table)
	{
		if ($table !== NULL
			AND ! $table instanceof Expression
			AND ! $table instanceof Identifier)
		{
			$table = new Table($table);
		}

		$this->like = $table;

		return $this;
	}

	/**
	 * Set the options of the table.
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

		$this->options = $options;

		return $this;
	}
}
