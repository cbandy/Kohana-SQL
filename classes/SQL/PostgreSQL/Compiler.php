<?php
namespace SQL\PostgreSQL;

use SQL\Compiler as SQL_Compiler;
use SQL\Expression;
use SQL\Identifier;
use SQL\Literal;
use SQL\Statement;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2011-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Compiler extends SQL_Compiler
{
	/**
	 * Maximum number of bytes allowed in an identifier
	 *
	 * @link http://www.postgresql.org/docs/current/static/sql-syntax-lexical.html#SQL-SYNTAX-IDENTIFIERS
	 */
	const MAX_LENGTH_IDENTIFIER = 63;

	public $placeholder = '/(?:\?|(?<=^|::|[^:]):\w++)/';

	/**
	 * Convert a generic [Expression] into a parameterized [Statement].
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
		 * until all parameters are unquoted literals.
		 *
		 * @param   Expression  $value
		 * @return  string  SQL fragment
		 */
		$parser['expression'] = function ($value) use ($compiler, &$parser)
		{
			// An expression without parameters is just raw SQL
			if (empty($value->parameters))
				return (string) $value;

			/**
			 * @var array Hash of SQL fragments by named parameter
			 */
			$fragments = NULL;
			$position = 0;

			return preg_replace_callback(
				$compiler->placeholder,
				function ($matches) use (&$fragments, &$parser, &$position, $value)
				{
					if ($matches[0] === '?')
					{
						return $parser['parameter'](
							$value->parameters[$position++]
						);
					}

					// Named parameter
					$param = $matches[0];

					if (isset($fragments[$param]))
						return $fragments[$param];

					return $fragments[$param] = $parser['parameter'](
						$value->parameters[$param]
					);
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

			return '$'.count($parser['parameters']);
		};

		/**
		 * Recursively expand a parameter value to an SQL fragment consisting
		 * only of placeholders.
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
}
