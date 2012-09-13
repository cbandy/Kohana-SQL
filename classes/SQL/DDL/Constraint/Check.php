<?php
namespace SQL\DDL\Constraint;

use SQL\Conditions;
use SQL\DDL\Constraint;

/**
 * Generic CHECK constraint.
 *
 * @package     SQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://www.postgresql.org/docs/current/static/ddl-constraints.html PostgreSQL
 * @link http://www.sqlite.org/syntaxdiagrams.html#table-constraint SQLite
 * @link http://msdn.microsoft.com/library/ms188258.aspx Transact-SQL
 */
class Check extends Constraint
{
	/**
	 * @var Conditions  Conditions
	 */
	public $conditions;

	/**
	 * @uses conditions()
	 *
	 * @param   Conditions  $conditions
	 */
	public function __construct($conditions = NULL)
	{
		parent::__construct();

		$this->conditions =& $this->parameters[':conditions'];

		$this->conditions($conditions);
	}

	public function __toString()
	{
		return parent::__toString().'CHECK (:conditions)';
	}

	/**
	 * Set the conditions of the constraint.
	 *
	 * @param   Conditions  $conditions
	 * @return  $this
	 */
	public function conditions($conditions)
	{
		$this->conditions = $conditions;

		return $this;
	}
}
