<?php
namespace SQL;

/**
 * Seekable result set backed by an array of rows.
 *
 * @package     SQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Result_Array extends Result_Seekable
{
	/**
	 * @var array   Rows
	 */
	protected $data;

	/**
	 * @param   array   $data   Rows
	 */
	public function __construct($data)
	{
		parent::__construct(count($data));

		$this->data = $data;
	}

	public function current()
	{
		return $this->data[$this->position];
	}

	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->data[$offset] : NULL;
	}

	public function to_array($key = NULL, $value = NULL)
	{
		if (($key === NULL AND $value === NULL) OR $this->count === 0)
			return $this->data;

		return parent::to_array($key, $value);
	}
}
