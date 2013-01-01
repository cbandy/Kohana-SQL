<?php
namespace SQL\MySQL\DDL;

use SQL\Expression;

/**
 * Hash-like expression for MySQL Index and Table options.
 *
 * @package     SQL
 * @subpackage  MySQL
 * @category    Data Definition Expressions
 *
 * @author      Chris Bandy
 * @copyright   (c) 2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @property    array   $values Hash of options
 */
class Options extends Expression implements \ArrayAccess
{
	/**
	 * @var array   Hash of options
	 */
	protected $values;

	/**
	 * @param   array   $values Hash of (option => value) pairs
	 */
	public function __construct($values = array())
	{
		$this->values = $values;
		array_walk($this->values, array($this, 'keyword'));
		$this->parameters = array_values($this->values);
	}

	public function __get($name)
	{
		if ($name === 'values')
			return $this->values;
	}

	public function __set($name, $value)
	{
		if ($name === 'values')
		{
			$this->values = $value;
			array_walk($this->values, array($this, 'keyword'));
			$this->parameters = array_values($this->values);
		}
	}

	public function __toString()
	{
		$options = array_map(
			function ($option) { return $option.' ?'; },
			array_keys($this->values)
		);

		return implode(' ', $options);
	}

	/**
	 * Wrap the value of certain options in an Expression.
	 *
	 * @param   mixed   $value  Option value
	 * @param   string  $option Option name
	 * @return  mixed|Expression
	 */
	protected function keyword(&$value, $option)
	{
		static $options_with_keyword_values = array(
			'CHARACTER SET'     => TRUE,
			'COLLATE'           => TRUE,
			'DEFAULT CHARACTER SET' => TRUE,
			'DEFAULT COLLATE'   => TRUE,
			'ENGINE'            => TRUE,
			'INSERT_METHOD'     => TRUE,
			'PACK_KEYS'         => TRUE,
			'ROW_FORMAT'        => TRUE,
			'STORAGE ENGINE'    => TRUE,
			'USING'             => TRUE,
			'WITH PARSER'       => TRUE,
		);

		if (isset($options_with_keyword_values[strtoupper($option)])
			AND ! $value instanceof Expression)
		{
			$value = new Expression($value);
		}

		return $value;
	}

	public function offsetExists($option)
	{
		return array_key_exists($option, $this->values);
	}

	public function offsetGet($option)
	{
		return $this->values[$option];
	}

	public function offsetSet($option, $value)
	{
		$this->values[$option] = $this->keyword($value, $option);
		$this->parameters = array_values($this->values);
	}

	public function offsetUnset($option)
	{
		unset($this->values[$option]);
		$this->parameters = array_values($this->values);
	}
}
