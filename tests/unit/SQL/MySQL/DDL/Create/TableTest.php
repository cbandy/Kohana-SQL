<?php
namespace SQL\MySQL\DDL;

use SQL\Expression;
use SQL\Identifier;
use SQL\Table;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Create_TableTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':columns' => NULL,
		':constraints' => NULL,
		':name' => NULL,
		':query' => NULL,
		':like' => NULL,
		':options' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL),
			array(array('a'), new Table('a')),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Table::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   Table   $name       Expected property
	 */
	public function test_constructor($arguments, $name)
	{
		$class = new \ReflectionClass('SQL\MySQL\DDL\Create_Table');
		$table = $class->newInstanceArgs($arguments);

		$this->assertEquals($name, $table->name);

		$this->assertSame('CREATE TABLE :name', (string) $table);
		$this->assertEquals(
			array_merge($this->parameters, array(':name' => $name)),
			$table->parameters
		);
	}

	public function provider_like()
	{
		return array(
			array(NULL, NULL, 'CREATE TABLE :name'),
			array('a', new Table('a'), 'CREATE TABLE :name LIKE :like'),
			array(
				new Expression('b'), new Expression('b'),
				'CREATE TABLE :name LIKE :like',
			),
			array(
				new Identifier('c'), new Identifier('c'),
				'CREATE TABLE :name LIKE :like',
			),
			array(
				new Table('d'), new Table('d'),
				'CREATE TABLE :name LIKE :like',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Table::like
	 *
	 * @dataProvider    provider_like
	 *
	 * @param   mixed   $argument   Argument
	 * @param   mixed   $like       Expected property
	 * @param   string  $value
	 */
	public function test_like($argument, $like, $value)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->like($argument));
		$this->assertEquals($like, $table->like);

		$this->assertSame($value, (string) $table);
		$this->assertEquals(
			array_merge($this->parameters, array(':like' => $like)),
			$table->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Table::like
	 *
	 * @dataProvider    provider_like
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_like_reset($argument)
	{
		$table = new Create_Table;
		$table->like($argument);

		$this->assertSame($table, $table->like(NULL));
		$this->assertNull($table->like);

		$this->assertSame('CREATE TABLE :name', (string) $table);
		$this->assertSame($this->parameters, $table->parameters);
	}

	public function provider_options()
	{
		return array(
			array(NULL, NULL, 'CREATE TABLE :name'),
			array(
				array('ENGINE' => 'InnoDB'),
				new Options(array('ENGINE' => 'InnoDB')),
				'CREATE TABLE :name :options',
			),
			array(
				new Options(array('AUTO_INCREMENT' => 5)),
				new Options(array('AUTO_INCREMENT' => 5)),
				'CREATE TABLE :name :options',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Table::options
	 *
	 * @dataProvider    provider_options
	 *
	 * @param   mixed   $argument   Argument
	 * @param   Options $options    Expected property
	 * @param   string  $value
	 */
	public function test_options($argument, $options, $value)
	{
		$table = new Create_Table;

		$this->assertSame($table, $table->options($argument));
		$this->assertEquals($options, $table->options);

		$this->assertSame($value, (string) $table);
		$this->assertEquals(
			array_merge($this->parameters, array(':options' => $options)),
			$table->parameters
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Table::options
	 *
	 * @dataProvider    provider_options
	 *
	 * @param   mixed   $argument   Argument
	 */
	public function test_options_reset($argument)
	{
		$table = new Create_Table;
		$table->options($argument);

		$this->assertSame($table, $table->options(NULL));
		$this->assertNull($table->options);

		$this->assertSame('CREATE TABLE :name', (string) $table);
		$this->assertSame($this->parameters, $table->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_Table::__toString
	 */
	public function test_toString()
	{
		$table = new Create_Table;
		$table
			->temporary()
			->if_not_exists()
			->options(array('a' => 'b'))
			->query(new Expression('c'));

		$this->assertSame(
			'CREATE TEMPORARY TABLE IF NOT EXISTS :name :options AS :query',
			(string) $table
		);

		$table->column(new Expression('d'));

		$this->assertSame(
			'CREATE TEMPORARY TABLE IF NOT EXISTS :name'
			.' (:columns) :options AS :query',
			(string) $table
		);

		$table->constraint(new Expression('e'));

		$this->assertSame(
			'CREATE TEMPORARY TABLE IF NOT EXISTS :name'
			.' (:columns, :constraints) :options AS :query',
			(string) $table
		);

		$table->like(new Expression('f'));

		$this->assertSame(
			'CREATE TEMPORARY TABLE IF NOT EXISTS :name LIKE :like',
			(string) $table
		);
	}
}
