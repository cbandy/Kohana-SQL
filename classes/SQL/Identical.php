<?php
namespace SQL;

/**
 * Null-safe equality comparator. Similar to = and <> except it does not yield
 * null when either operand is null.
 *
 * When the second argument is `'='`, this comparator yields true when both
 * operands are equal or null. Otherwise, this comparator yields true when the
 * operands are not equal or one of them is null.
 *
 * This is similar to `IS DISTINCT FROM` of SQL:1999 but doesn't handle
 * interesting types like rows, arrays or multisets.
 *
 * @package     SQL
 * @category    Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2013 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Identical extends Expression
{
	/**
	 * @param   mixed   $left       Left operand
	 * @param   string  $operator   Equality operator
	 * @param   mixed   $right      Right operand
	 */
	public function __construct($left, $operator, $right)
	{
		parent::__construct(
			$operator, array(':left' => $left, ':right' => $right)
		);
	}

	public function __toString()
	{
		$comparison = '(:left <> :right OR :left IS NULL OR :right IS NULL)';
		$null = '(:left IS NULL AND :right IS NULL)';

		return ($this->value === '=')
			? ('(NOT '.$comparison.' OR '.$null.')')
			: ('('.$comparison.' AND NOT '.$null.')');
	}
}
