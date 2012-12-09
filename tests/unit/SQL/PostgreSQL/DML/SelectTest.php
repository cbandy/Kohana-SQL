<?php
namespace SQL\PostgreSQL\DML;

use SQL\Column;
use SQL\Expression;

/**
 * @package     SQL
 * @subpackage  PostgreSQL
 * @author      Chris Bandy
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':from' => NULL,
		':groupby' => NULL,
		':having' => NULL,
		':limit' => NULL,
		':offset' => NULL,
		':orderby' => NULL,
		':values' => NULL,
		':where' => NULL,
		':distinct' => NULL,
	);

	public function provider_constructor()
	{
		return array(
			array(array(), NULL, 'SELECT *'),
			array(array(array('a')), array(new Column('a')), 'SELECT :values'),
			array(
				array(array('a', 'b')),
				array(new Column('a'), new Column('b')),
				'SELECT :values',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Select::__construct
	 *
	 * @dataProvider    provider_constructor
	 *
	 * @param   array   $arguments  Arguments
	 * @param   array   $values     Expected property
	 * @param   string  $value
	 */
	public function test_constructor($arguments, $values, $value)
	{
		$class = new \ReflectionClass('SQL\PostgreSQL\DML\Select');
		$select = $class->newInstanceArgs($arguments);

		$this->assertEquals($values, $select->values);

		$this->assertSame($value, (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':values' => $values)),
			$select->parameters
		);
	}

	public function provider_distinct()
	{
		return array(
			array(NULL, NULL, 'SELECT *'),
			array(array(), NULL, 'SELECT *'),
			array(FALSE, FALSE, 'SELECT *'),
			array(TRUE, TRUE, 'SELECT DISTINCT *'),

			array(
				array('a'), array(new Column('a')),
				'SELECT DISTINCT ON (:distinct) *',
			),
			array(
				array('a', 'b'), array(new Column('a'), new Column('b')),
				'SELECT DISTINCT ON (:distinct) *',
			),

			array(
				array(new Column('a')), array(new Column('a')),
				'SELECT DISTINCT ON (:distinct) *',
			),
			array(
				array(new Column('a'), new Column('b')),
				array(new Column('a'), new Column('b')),
				'SELECT DISTINCT ON (:distinct) *',
			),

			array(
				array(new Expression('a')), array(new Expression('a')),
				'SELECT DISTINCT ON (:distinct) *',
			),
			array(
				array(new Expression('a'), new Expression('b')),
				array(new Expression('a'), new Expression('b')),
				'SELECT DISTINCT ON (:distinct) *',
			),
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Select::distinct
	 *
	 * @dataProvider    provider_distinct
	 *
	 * @param   array|boolean   $argument   Argument
	 * @param   array           $distinct   Expected property
	 * @param   string          $value
	 */
	public function test_distinct($argument, $distinct, $value)
	{
		$select = new Select;

		$this->assertSame($select, $select->distinct($argument));
		$this->assertEquals($distinct, $select->distinct);

		$this->assertSame($value, (string) $select);
		$this->assertEquals(
			array_merge($this->parameters, array(':distinct' => $distinct)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Select::distinct
	 */
	public function test_distinct_default()
	{
		$select = new Select;

		$this->assertSame($select, $select->distinct());
		$this->assertTrue($select->distinct);

		$this->assertSame('SELECT DISTINCT *', (string) $select);
		$this->assertSame(
			array_merge($this->parameters, array(':distinct' => TRUE)),
			$select->parameters
		);
	}

	/**
	 * @covers  SQL\PostgreSQL\DML\Select::__toString
	 */
	public function test_toString()
	{
		$select = new Select;
		$select
			->distinct()
			->from('a')
			->where('b', '=', 'c')
			->group_by(array('d'))
			->having('e', '=', 'f')
			->order_by('g')
			->limit(1)
			->offset(1);

		$this->assertSame(
			'SELECT DISTINCT * FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $select
		);

		$select->value('h');

		$this->assertSame(
			'SELECT DISTINCT :values FROM :from WHERE :where GROUP BY :groupby'
			.' HAVING :having ORDER BY :orderby LIMIT :limit OFFSET :offset',
			(string) $select
		);

		$select->distinct(array('i'));

		$this->assertSame(
			'SELECT DISTINCT ON (:distinct) :values FROM :from WHERE :where'
			.' GROUP BY :groupby HAVING :having ORDER BY :orderby LIMIT :limit'
			.' OFFSET :offset',
			(string) $select
		);
	}
}
