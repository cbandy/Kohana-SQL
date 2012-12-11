<?php
namespace SQL\MySQL\DDL;

use SQL\DDL\Create_Index as SQL_Create_Index;
use SQL\Expression;

/**
 * CREATE INDEX statement for MySQL.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Data Definition Commands
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/create-index.html
 */
class Create_Index extends SQL_Create_Index
{
	/**
	 * @var string  Index type
	 */
	public $using;

	public function __toString()
	{
		$value = parent::__toString();

		if ($this->using)
		{
			$value .= ' USING '.$this->using;
		}

		return $value;
	}

	/**
	 * Set the kind of index to be created.
	 *
	 * @param   string  $type   UNIQUE, FULLTEXT, SPATIAL, etc.
	 * @return  $this
	 */
	public function type($type)
	{
		if ($type !== NULL)
		{
			$type = new Expression(strtoupper($type));
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * Set the index type.
	 *
	 * @param   string  $type   BTREE, HASH, etc.
	 * @return  $this
	 */
	public function using($type)
	{
		if ($type !== NULL)
		{
			$type = strtoupper($type);
		}

		$this->using = $type;

		return $this;
	}
}
