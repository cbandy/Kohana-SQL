<?php
namespace SQL;

/**
 * Structural metadata
 *
 * @package SQL
 *
 * @author      Chris Bandy
 * @copyright   (c) 2010-2012 Chris Bandy
 * @license     http://www.opensource.org/licenses/isc-license.txt
 */
class Metadata
{
	/**
	 * Return information about an SQL data type.
	 *
	 * @param   string  $type       SQL data type
	 * @param   string  $attribute  Attribute to return
	 * @return  array|mixed Array of attributes or an attribute value
	 */
	public function datatype($type, $attribute = NULL)
	{
		static $types = array(
			// SQL-92
			'bit'               => array('type' => 'string', 'exact' => TRUE),
			'bit varying'       => array('type' => 'string'),
			'char'              => array('type' => 'string', 'exact' => TRUE),
			'char varying'      => array('type' => 'string'),
			'character'         => array('type' => 'string', 'exact' => TRUE),
			'character varying' => array('type' => 'string'),
			'date'              => array('type' => 'string'),
			'dec'               => array('type' => 'float', 'exact' => TRUE),
			'decimal'           => array('type' => 'float', 'exact' => TRUE),
			'double precision'  => array('type' => 'float'),
			'float'             => array('type' => 'float'),
			'int'               => array(
				'type' => 'integer',
				'min' => '-2147483648', 'max' => '2147483647',
			),
			'integer'           => array(
				'type' => 'integer',
				'min' => '-2147483648', 'max' => '2147483647',
			),
			'interval'          => array('type' => 'string'),
			'national char'     => array('type' => 'string', 'exact' => TRUE),
			'national char varying' => array('type' => 'string'),
			'national character' => array('type' => 'string', 'exact' => TRUE),
			'national character varying'  => array('type' => 'string'),
			'nchar'             => array('type' => 'string', 'exact' => TRUE),
			'nchar varying'     => array('type' => 'string'),
			'numeric'           => array('type' => 'float', 'exact' => TRUE),
			'real'              => array('type' => 'float'),
			'smallint'          => array(
				'type' => 'integer',
				'min' => '-32768', 'max' => '32767',
			),
			'time'                      => array('type' => 'string'),
			'time with time zone'       => array('type' => 'string'),
			'timestamp'                 => array('type' => 'datetime'),
			'timestamp with time zone'  => array('type' => 'datetime'),
			'varchar'                   => array('type' => 'string'),

			// SQL:1999
			'binary large object'               => array('type' => 'binary'),
			'blob'                              => array('type' => 'binary'),
			'boolean'                           => array('type' => 'boolean'),
			'char large object'                 => array('type' => 'string'),
			'character large object'            => array('type' => 'string'),
			'clob'                              => array('type' => 'string'),
			'national character large object'   => array('type' => 'string'),
			'nchar large object'                => array('type' => 'string'),
			'nclob'                             => array('type' => 'string'),
			'time without time zone'            => array('type' => 'string'),
			'timestamp without time zone'       => array('type' => 'datetime'),

			// SQL:2003
			'bigint' => array(
				'type' => 'integer',
				'min' => '-9223372036854775808', 'max' => '9223372036854775807',
			),

			// SQL:2008
			'binary'            => array('type' => 'binary', 'exact' => TRUE),
			'binary varying'    => array('type' => 'binary'),
			'varbinary'         => array('type' => 'binary'),
		);

		if ($attribute !== NULL)
		{
			if (isset($types[$type][$attribute]))
				return $types[$type][$attribute];

			return NULL;
		}

		if (isset($types[$type]))
			return $types[$type];

		return array();
	}
}
