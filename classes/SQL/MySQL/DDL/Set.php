<?php
namespace SQL\MySQL\DDL;

use SQL\Expression;

/**
 * SET expression for MySQL.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://dev.mysql.com/doc/en/set.html
 */
class Set extends Expression
{
	/**
	 * @var array   List of permitted values
	 */
	public $values;

	/**
	 * @param   array   $values List of permitted values
	 */
	public function __construct($values = array())
	{
		$this->values =& $this->parameters;
		$this->values = $values;
	}

	public function __toString()
	{
		$count = count($this->values);

		return ($count > 0)
			? 'SET (?'.str_repeat(', ?', $count - 1).')'
			: 'SET ()';
	}
}
