<?php
namespace SQL;

/**
 * Thrown when a PHP driver method fails or the connection raises an error
 * condition.
 *
 * @package     SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://bugs.php.net/39615 Exception code cannot be string
 * @link http://bugs.php.net/51742 Notice when exception code is string
 */
class RuntimeException extends \RuntimeException
{
	public function __construct($message = '', $code = 0, $previous = NULL)
	{
		// Pass the integer code to the parent
		parent::__construct($message, (int) $code, $previous);

		// Save the unmodified code
		$this->code = $code;
	}
}
