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
		':options' => NULL,
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

	public function provider_options()
	{
		return array(
			array(NULL, NULL, 'CREATE INDEX :name ON :table (:columns)'),
			array(
				array('USING' => 'BTREE'),
				new Options(array('USING' => 'BTREE')),
				'CREATE INDEX :name ON :table (:columns) :options',
			),
			array(
				new Expression('something else'),
				new Expression('something else'),
				'CREATE INDEX :name ON :table (:columns) :options',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Index::options
	 *
	 * @dataProvider    provider_options
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Options $options    Expected property
	 * @param   string  $value
	 */
	public function test_options($argument, $options, $value)
	{
		$index = new Create_Index;

		$this->assertSame($index, $index->options($argument));
		$this->assertEquals($index->options, $options);

		$this->assertSame($value, (string) $index);
		$this->assertEquals(
			array_merge($this->parameters, array(':options' => $options)),
			$index->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Index::options
	 *
	 * @dataProvider    provider_options
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_options_reset($argument)
	{
		$index = new Create_Index;
		$index->options($argument);

		$this->assertSame($index, $index->options(NULL));
		$this->assertNull($index->options);

		$this->assertSame(
			'CREATE INDEX :name ON :table (:columns)', (string) $index
		);
		$this->assertSame($this->parameters, $index->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Index::__toString
	 */
	public function test_toString()
	{
		$index = new Create_Index;
		$index
			->type('unique')
			->options(array('using' => 'btree'));

		$this->assertSame(
			'CREATE :type INDEX :name ON :table (:columns) :options',
			(string) $index
		);
	}
}
