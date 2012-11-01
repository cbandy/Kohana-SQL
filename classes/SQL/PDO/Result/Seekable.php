<?php
namespace SQL\PDO;

use PDO;
use SQL\Result_Array;

/**
 * Seekable result set for a PDOStatement.
 *
 * Prefetches all data since scrollable cursors do not work for most drivers
 * and even PDOStatement->rowCount() should not be relied upon.
 *
 * @package     SQL
 * @subpackage  PDO
 * @category    Result Sets
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 *
 * @link http://bugs.php.net/44475  No MySQL cursor
 * @link http://bugs.php.net/44861  No PostgreSQL cursor
 * @link http://php.net/manual/pdostatement.rowcount
 */
class Result_Seekable extends Result_Array
{
	/**
	 * @param   PDOStatement    $statement  Executed statement
	 */
	public function __construct($statement)
	{
		parent::__construct($statement->fetchAll(PDO::FETCH_ASSOC));
	}
}
