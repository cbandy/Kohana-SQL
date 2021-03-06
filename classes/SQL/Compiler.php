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
	 * Whether or not a value is a [Literal], not a decendant of [Literal].
	 *
	 * @param   mixed   $value
	 * @return  boolean
	 */
	protected function is_a_literal($value)
	{
		return (is_object($value) AND get_class($value) === 'SQL\Literal');
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
		$compiler = $this;
		$parser = array('parameters' => array());

		/**
		 * Recursively replace array, [Expression] and [Identifier] parameters
		 * until all parameters are positional literals.
		 *
		 * @param   Expression  $value
		 * @return  string  SQL fragment
		 */
		$parser['expression'] = function ($value) use ($compiler, &$parser)
		{
			// An expression without parameters is just raw SQL
			if (empty($value->parameters))
				return (string) $value;

			$position = 0;

			return preg_replace_callback(
				$compiler->placeholder,
				function ($matches) use (&$parser, &$position, $value)
				{
					$param = ($matches[0] === '?') ? $position++ : $matches[0];

					return $parser['parameter']($value->parameters[$param]);
				},
				(string) $value
			);
		};

		/**
		 * Unwrap a [Literal] parameter before capturing the value.
		 *
		 * @param   mixed   $value  Unquoted parameter
		 * @return  string  SQL fragment
		 */
		$parser['literal'] = function (&$value) use ($compiler, &$parser)
		{
			if ($value instanceof Literal)
				return $parser['literal']($value->value);

			// Capture possible reference
			$parser['parameters'][] =& $value;

			return '?';
		};

		/**
		 * Recursively expand a parameter value to an SQL fragment consisting
		 * only of positional placeholders.
		 *
		 * @param   mixed   $value  Unquoted parameter
		 * @return  string  SQL fragment
		 */
		$parser['parameter'] = function (&$value) use ($compiler, &$parser)
		{
			if (is_array($value))
				return implode(', ', array_map($parser['parameter'], $value));

			if ($value instanceof Expression)
				return $parser['expression']($value);

			if ($value instanceof Identifier)
				return $compiler->quote($value);

			return $parser['literal']($value);
		};

		$result = $parser['expression']($statement);

		return new Statement($result, $parser['parameters']);
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
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @uses quote_literal()
	 *
	 * @param   array   $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_array($value)
	{
		return 'ARRAY['
			.implode(', ', array_map(array($this, 'quote_literal'), $value))
			.']';
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @param   string|Literal  $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_binary($value)
	{
		while ($value instanceof Literal)
		{
			$value = $value->value;
		}

		return "X'".current(unpack('H*', $value))."'";
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @param   boolean $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_boolean($value)
	{
		return $value ? "'1'" : "'0'";
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
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @param   DateTime    $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_datetime($value)
	{
		return $value->format("'Y-m-d H:i:s.uP'");
	}

	/**
	 * Quote an expression's parameters for inclusion in an SQL statement.
	 *
	 * @uses quote()
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
				$param = ($matches[0] === '?') ? $position++ : $matches[0];

				return $compiler->quote($parameters[$param]);
			},
			$value
		);
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @param   float   $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_float($value)
	{
		return sprintf('%E', $value);
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
	 * @param   integer $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_integer($value)
	{
		return (string) (int) $value;
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement. Dispatches to
	 * other quote_* methods.
	 *
	 * @uses quote_array()
	 * @uses quote_binary()
	 * @uses quote_boolean()
	 * @uses quote_datetime()
	 * @uses quote_float()
	 * @uses quote_integer()
	 * @uses quote_numeric()
	 * @uses quote_string()
	 *
	 * @param   mixed   $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_literal($value)
	{
		while ($this->is_a_literal($value))
		{
			$value = $value->value;
		}

		if ($value === NULL)
			return 'NULL';

		if (is_object($value))
		{
			if ($value instanceof \DateTime)
				return $this->quote_datetime($value);

			if ($value instanceof Literal\Binary)
				return $this->quote_binary($value);

			if ($value instanceof Literal\Numeric)
				return $this->quote_numeric($value);
		}
		else
		{
			if (is_bool($value))
				return $this->quote_boolean($value);

			if (is_int($value))
				return $this->quote_integer($value);

			if (is_float($value))
				return $this->quote_float($value);

			if (is_array($value))
				return $this->quote_array($value);
		}

		return $this->quote_string($value);
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @param   float|Literal   $value  Literal value to quote
	 * @param   integer         $scale  Number of digits in the fractional part
	 * @return  string  SQL fragment
	 */
	public function quote_numeric($value, $scale = NULL)
	{
		if ($scale === NULL and $value instanceof Literal\Numeric)
		{
			$scale = $value->scale;
		}

		$scale = ($scale === NULL) ? 4 : (int) $scale;

		while ($value instanceof Literal)
		{
			$value = $value->value;
		}

		return sprintf("%.{$scale}F", $value);
	}

	/**
	 * Quote a literal value for inclusion in an SQL statement.
	 *
	 * @param   string  $value  Literal value to quote
	 * @return  string  SQL fragment
	 */
	public function quote_string($value)
	{
		return "'".strtr($value, array("'" => "''"))."'";
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
