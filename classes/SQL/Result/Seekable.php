<?php
namespace SQL;

/**
 * Read-only, seekable result set iterator. Individual rows can be accessed
 * using brackets.
 *
 *     $result = $connection->execute($query);
 *
 *     // 5th row
 *     $row = $result[4];
 *
 * @package     SQL
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
abstract class Result_Seekable extends Result
	implements \ArrayAccess, \Countable, \SeekableIterator
{
	/**
	 * @var integer Number of rows
	 */
	protected $count;

	/**
	 * @param   integer $count  Number of rows
	 */
	public function __construct($count)
	{
		$this->count = $count;
	}

	/**
	 * The number of rows in the result set. Implements [Countable::count].
	 *
	 * @return  integer
	 */
	public function count()
	{
		return $this->count;
	}

	/**
	 * Whether or not an offset exists. Implements [ArrayAccess::offsetExists].
	 *
	 * @param   integer $offset
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		return ($offset >= 0 AND $offset < $this->count);
	}

	/**
	 * Return the row at the specified offset without moving the pointer.
	 * Returns NULL if the offset does not exist. Implements
	 * [ArrayAccess::offsetGet].
	 *
	 * @param   integer $offset
	 * @return  array
	 */
	abstract public function offsetGet($offset);

	/**
	 * Throw an exception. Implements [ArrayAccess::offsetSet].
	 *
	 * @throws Exception
	 */
	final public function offsetSet($offset, $value)
	{
		throw new \Exception('Result sets are read-only');
	}

	/**
	 * Throw an exception. Implements [ArrayAccess::offsetUnset].
	 *
	 * @throws Exception
	 */
	final public function offsetUnset($offset)
	{
		throw new \Exception('Result sets are read-only');
	}

	/**
	 * Move the current position to the previous row.
	 *
	 * @return  $this
	 */
	public function prev()
	{
		--$this->position;
		return $this;
	}

	/**
	 * Move the current position to the first row. Implements
	 * [Iterator::rewind].
	 *
	 * @return  $this
	 */
	public function rewind()
	{
		$this->position = 0;
		return $this;
	}

	/**
	 * Set the current position. Implements [SeekableIterator::seek].
	 *
	 * @throws  OutOfBoundsException
	 * @param   integer $position
	 * @return  $this
	 */
	public function seek($position)
	{
		if ( ! $this->offsetExists($position))
			throw new \OutOfBoundsException;

		$this->position = $position;
		return $this;
	}

	/**
	 * Whether or not the next call to current() will succeed. Implements
	 * [Iterator::valid].
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return $this->offsetExists($this->position);
	}
}
