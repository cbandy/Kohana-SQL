<?php
namespace SQL;

use PHPUnit_Framework_AssertionFailedError as AssertionFailedError;
use PHPUnit_Framework_IncompleteTestError as IncompleteTestError;
use PHPUnit_Framework_Test as Test;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestSuite as TestSuite;

class TestListener implements \PHPUnit_Framework_TestListener
{
	public function addError(Test $test, \Exception $e, $time)
	{
	}

	public function addFailure(Test $test, AssertionFailedError $e, $time)
	{
	}

	public function addIncompleteTest(Test $test, \Exception $e, $time)
	{
	}

	public function addSkippedTest(Test $test, \Exception $e, $time)
	{
	}

	protected function assertAssertions(TestCase $test, $time)
	{
		if ($test->getNumAssertions() <= 0)
		{
			$test->getTestResultObject()->addFailure(
				$test,
				new IncompleteTestError('This test does not perform any assertions'),
				$time
			);
		}
	}

	protected function assertCoversAnnotation(TestCase $test, $time)
	{
		$annotations = $test->getAnnotations();

		if (empty($annotations['method']['covers'])
			AND empty($annotations['method']['coversNothing']))
		{
			$test->getTestResultObject()->addFailure(
				$test,
				new IncompleteTestError('This test does not have a @covers tag'),
				$time
			);
		}
	}

	public function startTest(Test $test)
	{
	}

	public function endTest(Test $test, $time)
	{
		if ($test->getTestResultObject()->wasSuccessful())
		{
			$this->assertAssertions($test, $time);
			$this->assertCoversAnnotation($test, $time);
		}
	}

	public function startTestSuite(TestSuite $suite)
	{
	}

	public function endTestSuite(TestSuite $suite)
	{
	}
}
