<?php
namespace SQL\MySQL\DDL;

use SQL\Expression;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Create_IndexTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':columns' => NULL,
		':name' => NULL,
		':table' => NULL,
		':type' => NULL,
	);

	public function provider_type()
	{
		return array(
			array(NULL, NULL, 'CREATE INDEX :name ON :table (:columns)'),
			array(
				'fulltext',
				new Expression('FULLTEXT'),
				'CREATE :type INDEX :name ON :table (:columns)',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Index::type
	 *
	 * @dataProvider    provider_type
	 *
	 * @param   string      $argument   Argument
	 * @param   Expression  $type       Expected property
	 * @param   string      $value
	 */
	public function test_type($argument, $type, $value)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->type($argument));
		$this->assertEquals($type, $index->type);

		$this->assertSame($value, (string) $index);
		$this->assertEquals(
			array_merge($this->parameters, array(':type' => $type)),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Index::type
	 *
	 * @dataProvider    provider_type
	 *
	 * @param   string  $argument   Argument
	 */
	public function test_type_reset($argument)
	{
		$index = new Create_Index;
		$index->type($argument);

		$this->assertSame($index, $index->type(NULL));
		$this->assertNull($index->type);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertSame($this->parameters, $index->parameters);
	}

	public function provider_using()
	{
		return array(
			array(NULL, NULL, 'CREATE INDEX :name ON :table (:columns)'),
			array(
				'btree', 'BTREE',
				'CREATE INDEX :name ON :table (:columns) USING BTREE',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Index::__toString
	 * @covers  SQL\MySQL\DDL\Create_Index::using
	 *
	 * @dataProvider    provider_using
	 *
	 * @param   string  $argument   Argument
	 * @param   string  $using      Expected property
	 * @param   string  $value
	 */
	public function test_using($argument, $using, $value)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->using($argument));
		$this->assertSame($index->using, $using);

		$this->assertSame($value, (string) $index);
		$this->assertSame($this->parameters, $index->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Index::using
	 *
	 * @dataProvider    provider_using
	 *
	 * @param   string  $argument   Argument
	 */
	public function test_using_reset($argument)
	{
		$index = new Create_Index;
		$index->using($argument);

		$this->assertSame($index, $index->using(NULL));
		$this->assertNull($index->using);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertSame($this->parameters, $index->parameters);
	}
}
