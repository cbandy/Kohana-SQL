<?php
namespace SQL\MySQL\DDL;

use SQL\DDL\Create_Index as SQL_Create_Index;
use SQL\Expression;

/**
 * CREATE INDEX statement for MySQL. Allows index type and extra options.
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
	 * @var Options Options of the index
	 */
	public $options;

	public function __construct($name = NULL, $table = NULL, $columns = NULL)
	{
		parent::__construct($name, $table, $columns);

		$this->options =& $this->parameters[':options'];
	}

	public function __toString()
	{
		$value = parent::__toString();

		if ($this->options)
		{
			$value .= ' :options';
		}

		return $value;
	}

	/**
	 * Set the options of the index.
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
}
