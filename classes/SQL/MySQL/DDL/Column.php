<?php
namespace SQL\MySQL\DDL;

use SQL\DDL\Column as SQL_DDL_Column;
use SQL\DDL\Constraint\Foreign;

/**
 * Column definition for MySQL. Allows AUTO_INCREMENT and COMMENT to be set.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-table.html
 * @link http://dev.mysql.com/doc/en/example-auto-increment.html
 */
class Column extends SQL_DDL_Column
{
	/**
	 * @var boolean Whether or not the value of the column can be generated as
	 *  though from a sequence
	 */
	public $auto_increment;

	/**
	 * @var string  Comment on the column, shown during some SHOW statements
	 */
	public $comment;

	public function __construct($name = NULL, $type = NULL)
	{
		parent::__construct($name, $type);

		$this->comment =& $this->parameters[':comment'];
	}

	public function __toString()
	{
		$value = ':name :type';

		if ($this->has_default)
		{
			$value .= ' DEFAULT :default';
		}

		if ($this->not_null)
		{
			$value .= ' NOT NULL';
		}

		if ($this->auto_increment)
		{
			$value .= ' AUTO_INCREMENT';
		}

		if ($this->comment)
		{
			$value .= ' COMMENT :comment';
		}

		if ($this->constraints)
		{
			$value .= ' :constraints';
		}

		return $value;
	}

	/**
	 * Set whether or not the default value of the column should be automatically generated.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function auto_increment($value = TRUE)
	{
		$this->auto_increment = $value;

		return $this;
	}

	/**
	 * Set the comment for the column.
	 *
	 * @param   string  $value
	 * @return  $this
	 */
	public function comment($value)
	{
		$this->comment = $value;

		return $this;
	}

	public function constraints($constraints)
	{
		if (is_array($constraints))
		{
			usort($constraints, function ($a, $b)
			{
				return ($a instanceof Foreign) ? 1 : -1;
			});
		}

		return parent::constraints($constraints);
	}
}
