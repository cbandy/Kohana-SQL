<?php
namespace SQL\MySQL;

use SQL\Result as SQL_Result;

/**
 * Forward-only result set for a MySQLi result.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://php.net/manual/class.mysqli-result
 */
class Result extends SQL_Result
{
	/**
	 * @var array
	 */
	protected $current_row;

	/**
	 * @var mysqli_result
	 */
	protected $result;

	/**
	 * @param   mysqli_result   $result
	 */
	public function __construct($result)
	{
		$this->result = $result;
		$this->fetch();
	}

	public function __destruct()
	{
		$this->result->free();
	}

	public function current()
	{
		return $this->current_row;
	}

	protected function fetch()
	{
		$this->current_row = $this->result->fetch_assoc();
	}

	public function next()
	{
		$this->fetch();
		return parent::next();
	}

	public function valid()
	{
		return ($this->current_row !== NULL);
	}
}
