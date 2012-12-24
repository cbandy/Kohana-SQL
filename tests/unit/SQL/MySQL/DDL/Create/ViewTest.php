<?php
namespace SQL\MySQL\DDL;

/**
 * @package     SQL
 * @subpackage  MySQL
 * @author      Chris Bandy
 */
class Create_ViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array   Default parameters
	 */
	protected $parameters = array(
		':columns' => NULL,
		':name' => NULL,
		':query' => NULL,
	);

	public function provider_algorithm()
	{
		return array(
			array(NULL, NULL, 'CREATE VIEW :name AS :query'),
			array(
				'merge', 'MERGE',
				'CREATE ALGORITHM = MERGE VIEW :name AS :query',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_View::algorithm
	 *
	 * @dataProvider    provider_algorithm
	 *
	 * @param   string  $argument   Argument
	 * @param   string  $algorithm  Expected property
	 * @param   string  $value
	 */
	public function test_algorithm($argument, $algorithm, $value)
	{
		$view = new Create_View;

		$this->assertSame($view, $view->algorithm($argument));
		$this->assertSame($algorithm, $view->algorithm);

		$this->assertSame($value, (string) $view);
		$this->assertSame($this->parameters, $view->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_View::algorithm
	 *
	 * @dataProvider    provider_algorithm
	 *
	 * @param   string  $argument   Argument
	 */
	public function test_algorithm_reset($argument)
	{
		$view = new Create_View;
		$view->algorithm($argument);

		$this->assertSame($view, $view->algorithm(NULL));
		$this->assertNull($view->algorithm);

		$this->assertSame('CREATE VIEW :name AS :query', (string) $view);
		$this->assertSame($this->parameters, $view->parameters);
	}

	public function provider_check()
	{
		return array(
			array(NULL, NULL, 'CREATE VIEW :name AS :query'),
			array(
				'cascaded', 'CASCADED',
				'CREATE VIEW :name AS :query WITH CASCADED CHECK OPTION',
			),
		);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_View::check
	 *
	 * @dataProvider    provider_check
	 *
	 * @param   string  $argument   Argument
	 * @param   string  $check      Expected property
	 * @param   string  $value
	 */
	public function test_check($argument, $check, $value)
	{
		$view = new Create_View;

		$this->assertSame($view, $view->check($argument));
		$this->assertSame($check, $view->check);

		$this->assertSame($value, (string) $view);
		$this->assertSame($this->parameters, $view->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_View::check
	 *
	 * @dataProvider    provider_check
	 *
	 * @param   string  $argument   Argument
	 */
	public function test_check_reset($argument)
	{
		$view = new Create_View;
		$view->check($argument);

		$this->assertSame($view, $view->check(NULL));
		$this->assertNull($view->check);

		$this->assertSame('CREATE VIEW :name AS :query', (string) $view);
		$this->assertSame($this->parameters, $view->parameters);
	}

	/**
	 * @covers  SQL\MySQL\DDL\Create_View::__toString
	 */
	public function test_toString()
	{
		$view = new Create_View;
		$view
			->replace()
			->algorithm('a')
			->columns(array('b'))
			->check('c');

		$this->assertSame(
			'CREATE OR REPLACE ALGORITHM = A VIEW :name (:columns) AS :query'
			.' WITH C CHECK OPTION',
			(string) $view
		);
	}
}
