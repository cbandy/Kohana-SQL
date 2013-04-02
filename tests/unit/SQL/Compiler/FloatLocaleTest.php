<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 */
class Compiler_FloatLocaleTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var string  Backup of the LC_NUMERIC locale
	 */
	protected $locale_numeric;

	public function setup()
	{
		$this->locale_numeric = setlocale(LC_NUMERIC, '0');

		if ( ! setlocale(LC_NUMERIC, array('de_DE', 'deu', 'fr_FR', 'fra', 'nl_NL', 'nld')))
			return $this->markTestSkipped('Lacking necessary locale');
	}

	public function teardown()
	{
		setlocale(LC_NUMERIC, $this->locale_numeric);
	}

	/**
	 * @covers  SQL\Compiler::quote_float
	 */
	public function test_decimal_separator_is_period()
	{
		$compiler = new Compiler;

		$this->assertSame('1.234500E+1', $compiler->quote_float(12.345));
	}

	/**
	 * @covers  SQL\Compiler::quote_float
	 */
	public function test_thousand_separator_is_absent()
	{
		$compiler = new Compiler;

		$this->assertSame('1.234500E+4', $compiler->quote_float(12345));
	}
}
