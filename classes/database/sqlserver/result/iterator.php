<?php

/**
 * @package     RealDatabase
 * @subpackage  Microsoft SQL Server
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Database_SQLServer_Result_Iterator extends Database_Result_Iterator
{
	/**
	 * @var mixed   Result object class
	 */
	protected $_as_object;

	/**
	 * @var integer|Database_SQLServer_Result
	 */
	protected $_current;

	/**
	 * @var resource    From sqlsrv_prepare() or sqlsrv_query()
	 */
	protected $_statement;

	/**
	 * @param   resource    $statement  From sqlsrv_prepare() or sqlsrv_query()
	 * @param   mixed       $as_object  Result object class, TRUE for stdClass, FALSE for associative array
	 */
	public function __construct($statement, $as_object)
	{
		$this->_as_object = ($as_object === TRUE) ? 'stdClass' : $as_object;
		$this->_statement = $statement;

		$this->_current();
	}

	protected function _current()
	{
		if (sqlsrv_num_fields($this->_statement))
		{
			$rows = array();

			if ($this->_as_object)
			{
				while ($row = sqlsrv_fetch_object($this->_statement, $this->_as_object))
				{
					$rows[] = $row;
				}
			}
			else
			{
				while ($row = sqlsrv_fetch_array($this->_statement, SQLSRV_FETCH_ASSOC))
				{
					$rows[] = $row;
				}
			}

			$this->_current = new Database_Result_Array($rows, $this->_as_object);
		}
		else
		{
			$this->_current = sqlsrv_rows_affected($this->_statement);

			if ($this->_current < 0)
				$this->_current = 0;
		}
	}

	public function current()
	{
		return $this->_current;
	}

	public function next()
	{
		if ( ! $result = sqlsrv_next_result($this->_statement))
		{
			$this->_current = NULL;

			if ($result === FALSE)
				throw new Database_SQLServer_Exception;
		}
		else
		{
			$this->_current();
		}

		return parent::next();
	}

	public function valid()
	{
		return ($this->_current !== NULL);
	}
}
