<?php

/**
 * @package     RealDatabase
 * @subpackage  SQLite
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.sqlite.org/lang_createtable.html
 */
class Database_SQLite_Create_Table extends Database_Command_Create_Table
{
	/**
	 * @var boolean
	 */
	protected $_if_not_exists;

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->_temporary)
		{
			$value .= ' TEMPORARY';
		}

		$value .= ' TABLE';

		if ($this->_if_not_exists)
		{
			$value .= ' IF NOT EXISTS';
		}

		$value .= ' :name';

		if ( ! empty($this->parameters[':query']))
		{
			$value .= ' AS :query';
		}
		else
		{
			$value .= ' (:columns';

			if ( ! empty($this->parameters[':constraints']))
			{
				$value .= ', :constraints';
			}

			$value .= ')';
		}

		return $value;
	}

	/**
	 * Set whether or not an error should be suppressed if the object exists.
	 *
	 * @param   boolean $value
	 * @return  $this
	 */
	public function if_not_exists($value = TRUE)
	{
		$this->_if_not_exists = $value;

		return $this;
	}
}