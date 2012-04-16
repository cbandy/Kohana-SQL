<?php
namespace SQL;

/**
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Compiler
{
	/**
	 * @var string  PCRE which matches expression placeholders
	 */
	public $placeholder = '/(?:\?|:\w++)/';

	/**
	 * @var string  Left character used to quote identifiers
	 */
	public $quote_left = '"';

	/**
	 * @var string  Right character used to quote identifiers
	 */
	public $quote_right = '"';

	/**
	 * @var string  Prefix added to tables when quoting
	 */
	public $table_prefix;

	/**
	 * @param   string          $table_prefix   Prefix added to tables
	 *     when quoting
	 * @param   string|array    $quote          Character used to quote
	 *     identifiers or an array of the left and right characters
	 */
	public function __construct($table_prefix = '', $quote = NULL)
	{
		$this->table_prefix = $table_prefix;

		if ($quote !== NULL)
		{
			if (is_array($quote))
			{
				$this->quote_left = reset($quote);
				$this->quote_right = next($quote);
			}
			else
			{
				$this->quote_left = $this->quote_right = $quote;
			}
		}
	}

	/**
	 * Recursively replace array, [Expression] and [Identifier] parameters
	 * until all parameters are positional literals.
	 *
	 * @param   string  $statement          SQL statement with (or without)
	 *     placeholders
	 * @param   array   $parameters         Unquoted parameters
	 * @param   array   $result_parameters  Parameters for the resulting
	 *     statement
	 * @return  string  SQL statement
	 */
	protected function parse($statement, $parameters, &$result_parameters)
	{
		// Trying to maintain context between calls (and recurse) using
		// preg_replace_callback is too complicated. Capturing the placeholder
		// offsets allows us to iterate over a single expression and recurse
		// using the call stack.
		$chunks = preg_split(
			$this->placeholder, $statement, NULL, PREG_SPLIT_OFFSET_CAPTURE
		);

		$position = 0;
		$prev = $chunks[0];
		$result = $prev[0];

		for ($i = 1, $max = count($chunks); $i < $max; ++$i)
		{
			if ($statement[$chunks[$i][1] - 1] === '?')
			{
				// Character before the current chunk is a question mark
				$placeholder = $position++;
			}
			else
			{
				// End of the previous chunk
				$offset = $prev[1] + strlen($prev[0]);

				// Text between the current chunk and the previous one
				$placeholder = substr(
					$statement, $offset, $chunks[$i][1] - $offset
				);
			}

			$prev = $chunks[$i];
			$result .= $this->parse_value(
				$parameters, $placeholder, $result_parameters
			).$prev[0];
		}

		return $result;
	}

	/**
	 * Recursively expand a parameter value to an SQL fragment consisting only
	 * of positional placeholders.
	 *
	 * @param   array           $array              Unquoted parameters
	 * @param   integer|string  $key                Index of the parameter value
	 *     to parse
	 * @param   array           $result_parameters  Parameters for the resulting
	 *     fragment
	 * @return  string  SQL fragment
	 */
	protected function parse_value($array, $key, &$result_parameters)
	{
		$value = $array[$key];

		if (is_array($value))
		{
			if (empty($value))
				return '';

			$result = array();

			foreach ($value as $k => $v)
			{
				$result[] = $this->parse_value($value, $k, $result_parameters);
			}

			return implode(', ', $result);
		}

		if ($value instanceof Expression)
		{
			return $this->parse(
				(string) $value, $value->parameters, $result_parameters
			);
		}

		if ($value instanceof Identifier)
			return $this->quote($value);

		// Capture possible reference
		$result_parameters[] =& $array[$key];

		return '?';
	}

	/**
	 * Convert a generic [Expression] into a natively parameterized
	 * [Statement]. Parameter names are driver-specific, but the default
	 * implementation replaces all [Expression] and [Identifier] parameters
	 * so that the remaining parameters are a 0-indexed array of literals.
	 *
	 * @param   Expression  $statement  SQL statement
	 * @return  Statement
	 */
	public function parse_statement($statement)
	{
		$parameters = array();

		$statement = $this->parse(
			(string) $statement, $statement->parameters, $parameters
		);

		return new Statement($statement, $parameters);
	}

	/**
	 * Quote a value for inclusion in an SQL statement. Dispatches to other
	 * quote_* methods.
	 *
	 * @uses quote_column()
	 * @uses quote_expression()
	 * @uses quote_identifier()
	 * @uses quote_literal()
	 * @uses quote_table()
	 *
	 * @param   mixed   $value  Value to quote
	 * @return  string  SQL fragment
	 */
	public function quote($value)
	{
		if (is_array($value))
		{
			return $value
				? implode(', ', array_map(array($this, __FUNCTION__), $value))
				: '';
		}

		if (is_object($value))
		{
			if ($value instanceof Expression)
				return $this->quote_expression($value);

			if ($value instanceof Column)
				return $this->quote_column($value);

			if ($value instanceof Table)
				return $this->quote_table($value);

			if ($value instanceof Identifier)
				return $this->quote_identifier($value);
		}

		return $this->quote_literal($value);
	}

	/**
	 * Quote a column identifier for inclusion in an SQL statement. Adds the
	 * table prefix unless the namespace is an [Identifier].
	 *
	 * @uses quote_identifier()
	 * @uses quote_table()
	 *
	 * @param   array|string|Identifier $value  Column to quote
	 * @return  string  SQL fragment
	 */
	public function quote_column($value)
	{
		if ($value instanceof Identifier)
		{
			$namespace = $value->namespace;
			$value = $value->name;
		}
		elseif (is_array($value))
		{
			$namespace = $value;
			$value = array_pop($namespace);
		}
		else
		{
			$namespace = explode('.', $value);
			$value = array_pop($namespace);
		}

		if (empty($namespace))
		{
			$prefix = '';
		}
		elseif ($namespace instanceof Table
			OR ! $namespace instanceof Identifier)
		{
			$prefix = $this->quote_table($namespace).'.';
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		if ($value === '*')
		{
			$value = $prefix.$value;
		}
		else
		{
			$value = $prefix.$this->quote_left.$value.$this->quote_right;
		}

		return $value;
	}

	/**
	 * Quote an expression's parameters for inclusion in an SQL statement.
	 *
	 * @param   Expression  $value  Expression to quote
	 * @return  string  SQL fragment
	 */
	public function quote_expression($value)
	{
		$parameters = $value->parameters;
		$value = (string) $value;

		// An expression without parameters is just raw SQL
		if (empty($parameters))
			return $value;

		$compiler = $this;
		$position = 0;

		return preg_replace_callback(
			$this->placeholder,
			function ($matches) use ($compiler, $parameters, &$position)
			{
				$placeholder = ($matches[0] === '?')
					? $position++
					: $matches[0];

				return $compiler->quote($parameters[$placeholder]);
			},
			$value
		);
	}

	/**
	 * Quote an identifier for inclusion in an SQL statement.
	 *
	 * @param   array|string|Identifier $value  Identifier to quote
	 * @return  string  SQL fragment
	 */
	public function quote_identifier($value)
	{
		if ($value instanceof Identifier)
		{
			$namespace = $value->namespace;
			$value = $value->name;
		}
		elseif (is_array($value))
		{
			$namespace = $value;
			$value = array_pop($namespace);
		}
		else
		{
			$namespace = explode('.', $value);
			$value = array_pop($namespace);
		}

		if (empty($namespace))
		{
			$prefix = '';
		}
		elseif (is_array($namespace))
		{
			$prefix = '';

			foreach ($namespace as $part)
			{
				// Quote each of the parts
				$prefix .= $this->quote_left.$part.$this->quote_right.'.';
			}
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		$value = $prefix.$this->quote_left.$value.$this->quote_right;

		return $value;
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @param   mixed   $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_literal($value)
	{
		if ($value === NULL)
		{
			$value = 'NULL';
		}
		elseif ($value === TRUE)
		{
			$value = "'1'";
		}
		elseif ($value === FALSE)
		{
			$value = "'0'";
		}
		elseif (is_int($value))
		{
			$value = (string) $value;
		}
		elseif (is_float($value))
		{
			$value = sprintf('%F', $value);
		}
		elseif (is_array($value))
		{
			$value = '('
				.implode(', ', array_map(array($this, __FUNCTION__), $value))
				.')';
		}
		else
		{
			$value = "'$value'";
		}

		return $value;
	}

	/**
	 * Quote a table identifier for inclusion in an SQL statement. Adds the
	 * table prefix.
	 *
	 * @uses quote_identifier()
	 *
	 * @param   array|string|Identifier $value  Table to quote
	 * @return  string  SQL fragment
	 */
	public function quote_table($value)
	{
		if ($value instanceof Identifier)
		{
			$namespace = $value->namespace;
			$value = $value->name;
		}
		elseif (is_array($value))
		{
			$namespace = $value;
			$value = array_pop($namespace);
		}
		else
		{
			$namespace = explode('.', $value);
			$value = array_pop($namespace);
		}

		if (empty($namespace))
		{
			$prefix = '';
		}
		else
		{
			$prefix = $this->quote_identifier($namespace).'.';
		}

		$value = $prefix
			.$this->quote_left.$this->table_prefix.$value.$this->quote_right;

		return $value;
	}
}
