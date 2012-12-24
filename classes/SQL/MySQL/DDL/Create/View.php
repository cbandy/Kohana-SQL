<?php
namespace SQL\MySQL\DDL;

use SQL\DDL\Create_View as SQL_Create_View;

/**
 * CREATE VIEW statement for MySQL. Allows the ALGORITHM and CHECK OPTION to be
 * specified.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-view.html
 */
class Create_View extends SQL_Create_View
{
	/**
	 * @var string  MERGE, TEMPTABLE or UNDEFINED
	 */
	public $algorithm;

	/**
	 * @var string  CASCADED or LOCAL check option
	 */
	public $check;

	public function __toString()
	{
		$value = 'CREATE';

		if ($this->replace)
		{
			$value .= ' OR REPLACE';
		}

		if ($this->algorithm)
		{
			$value .= ' ALGORITHM = '.$this->algorithm;
		}

		$value .= ' VIEW :name';

		if ($this->columns)
		{
			$value .= ' (:columns)';
		}

		$value .= ' AS :query';

		if ($this->check)
		{
			$value .= ' WITH '.$this->check.' CHECK OPTION';
		}

		return $value;
	}

	/**
	 * Set the algorithm used to process the view.
	 *
	 * @link http://dev.mysql.com/doc/en/view-algorithms.html
	 *
	 * @param   string  $value  MERGE, TEMPTABLE or UNDEFINED
	 * @return  $this
	 */
	public function algorithm($value)
	{
		if ($value !== NULL)
		{
			$value = strtoupper($value);
		}

		$this->algorithm = $value;

		return $this;
	}

	/**
	 * Set the CHECK OPTION clause for an updatable view.
	 *
	 * @link http://dev.mysql.com/doc/en/view-updatability.html
	 *
	 * @param   string  $value  CASCADED or LOCAL
	 * @return  $this
	 */
	public function check($value)
	{
		if ($value !== NULL)
		{
			$value = strtoupper($value);
		}

		$this->check = $value;

		return $this;
	}
}
