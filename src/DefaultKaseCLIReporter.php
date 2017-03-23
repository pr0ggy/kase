<?php

namespace Kase;

use SebastianBergmann\Comparator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Built-in simple CLI reporter for the Kase testing framework
 *
 * @package Kase
 */
class DefaultKaseCLIReporter implements Reporter
{
    /**
     * The CLI OutputInterface used to write reported info
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Used to generate and report diffs between expected/actual values from test cases
     *
     * @var SebastianBergmann\Comparator\Factory
     */
    private $comparatorFactory;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->comparatorFactory = new Comparator\Factory();
    }

    /**
     * Writes Kase version to the console
     */
    public function registerTestRunnerInitialization()
    {
        $this->writeBlankLineToOutput();
        $this->writeLineToOutput('Kase '.VERSION);
        $this->writeBlankLineToOutput();
    }

    /**
     * Writes suite description to the reporter's OutputInterface instance just before running the
     * tests within a suite
     *
     * @param  string $suiteDescription
     */
    public function registerSuiteExecutionInitiation($suiteDescription)
    {
        $this->writeLineToOutput('///////////////////////////////////////////');
        $this->writeLineToOutput('//');
        $this->writeLineToOutput("//  {$suiteDescription}");
        $this->writeLineToOutput('//');
    }

    /**
     * @param  string $text text to write to the reporter's OutputInterface instance
     */
    protected function writeToOutput($text)
    {
        $this->output->write($text);
    }

    /**
     * @param  string $text text to write to the reporter's OutputInterface instance
     */
    protected function writeLineToOutput($text)
    {
        $this->output->writeln($text);
    }

    /**
     * Write a blank line to the reporter's OutputInterface instance
     */
    protected function writeBlankLineToOutput()
    {
        $this->writeLineToOutput('');
    }

    /**
     * Writes information about a passing test to the reporter's OutputInterface dependency
     *
     * @param  string $testDescription the description of the passing test
     */
    public function registerPassedTest($testDescription)
    {
        $this->writeLineToOutput(
            $this->inSuccessFormat("[PASS] {$testDescription}")
        );
    }

    /**
     * @param  string $text text that should receive 'success' formatting
     * @return string       the given text in 'success' format
     */
    protected function inSuccessFormat($text)
    {
        return "<info>{$text}</info>";
    }

    /**
     * Writes information about a skipped test to the reporter's OutputInterface dependency
     *
     * @param  string $testDescription the description of the skipped test
     */
    public function registerSkippedTest($testDescription)
    {
        $this->writeLineToOutput(
            $this->inCommentFormat("[SKIP] {$testDescription}")
        );
    }

    /**
     * @param  string $text text that should receive 'comment'/'info' formatting
     * @return string       the given text in 'comment'/'info' format
     */
    protected function inCommentFormat($text)
    {
        return "<comment>{$text}</comment>";
    }

    /**
     * Writes information about a failed test to the reporter's OutputInterface dependency
     *
     * @param  string                     $testDescription the description of the failed test
     * @param  ValidationFailureException $exception       the validation exception resulting in the failure
     */
    public function registerFailedTest($testDescription, ValidationFailureException $exception)
    {
        $this->writeLineToOutput(
            $this->inErrorFormat("[FAIL] {$testDescription}")
        );
    }

    /**
     * Writes details about a validation exception resulting in a test failure
     *
     * The details may include a simple failure message specified in the test, or the failure message
     * along with a diff printout of actual/expected values.
     *
     * @param  ValidationFailureException $exception the validation exception resulting in the failure
     */
    protected function outputTestFailureDetails(ValidationFailureException $exception)
    {
        $this->writeToOutput(
            $this->inErrorFormat($exception->getMessage())
        );

        $expectedValue = $exception->getExpectedValue();
        if (is_null($expectedValue)) {
            $this->writeBlankLineToOutput();
            return;
        }

        $actualValue = $exception->getActualValue();
        $comparator = $this->comparatorFactory->getComparatorFor($expectedValue, $actualValue);
        try {
            $comparator->assertEquals($expectedValue, $actualValue);
        } catch (Comparator\ComparisonFailure $failure) {
            $this->writeLineToOutput(
                $this->inErrorFormat($failure->getDiff())
            );
        }
    }

    /**
     * @param  string $text text that should receive 'error' formatting
     * @return string       the given text in 'error' format
     */
    protected function inErrorFormat($text)
    {
        return "<error>{$text}</error>";
    }

    /**
     * Writes details of any unexpected exception encountered when executing a test to the reporter's
     * OutputInterface instance
     *
     * @param  \Exception $exception the encountered exception
     */
    public function registerUnexpectedException(\Exception $exception)
    {
        $this->writeLineToOutput(
            $this->inErrorFormat("[FAIL] Unexpected {$exception}")
        );
    }

    /**
     * Writes details about a completed suite to the reporter's OutputInterface instance
     *
     * These details include the number of passed/failed/skipped test as well as the duration of
     * all tests in the suite.
     *
     * @param  string $suiteDescription
     * @param  array  $suiteMetrics     dictionary of completed suite metrics
     */
    public function registerSuiteExecutionCompletion($suiteDescription, array $suiteMetrics)
    {
        $executionDurationInSeconds = ($suiteMetrics['executionEndTime'] - $suiteMetrics['executionStartTime']);
        $executionDurationText = (($executionDurationInSeconds < 0.001) ? 'less than 1ms' : number_format($executionDurationInSeconds, 3).' seconds');

        $this->writeLineToOutput('//');
        $this->writeLineToOutput("// {$suiteDescription} tested in {$executionDurationText}");
        $this->writeLineToOutput(
            "// {$this->inSuccessFormat($suiteMetrics['passedTestCount'])} Passed, {$this->inErrorFormat(count($suiteMetrics['failedTests']))} Failed, {$this->inCommentFormat(count($suiteMetrics['skippedTests']))} Skipped"
        );
        $this->writeLineToOutput('///////////////////////////////////////////');
        $this->writeBlankLineToOutput();
        $this->writeBlankLineToOutput();
    }

    /**
     * Writes testing summary including details about metrics gathered for all executed suites to
     * the reporter's OutputInterface instance
     *
     * These details include total number of passed/failed/skipped tests, as well as details on the
     * failed tests from each executed suite.
     *
     * @param  array  $suiteMetricsList list of metrics package from each executed suite
     */
    public function registerSuiteMetricsSummary(array $suiteMetricsList)
    {
        if (empty($suiteMetricsList)) {
            $this->writeLineToOutput('No test files found');
            return;
        }

        // GENERATE SUITE METRIC SUMMARY
        $suiteSummaryReducer = function ($summary, $individualSuiteMetrics) {
            $summary['passedTestCount'] += $individualSuiteMetrics['passedTestCount'];
            $summary['failedTestCount'] += count($individualSuiteMetrics['failedTests']);
            $summary['skippedTestCount'] += count($individualSuiteMetrics['skippedTests']);
            $summary['duration'] += ($individualSuiteMetrics['executionEndTime'] - $individualSuiteMetrics['executionStartTime']);
            return $summary;
        };

        $resultSummary = array_reduce(
            $suiteMetricsList,
            $suiteSummaryReducer,
            [
                'passedTestCount' => 0,
                'failedTestCount' => 0,
                'skippedTestCount' => 0,
                'duration' => 0
            ]
        );

        $executionDurationText = (($resultSummary['duration'] < 0.001) ? 'less than 1ms' : number_format($resultSummary['duration'], 3).' seconds');

        // PRINT THE TESTING SUMMARY
        $this->writeLineToOutput('///////////////////////////////////////////');
        $this->writeLineToOutput('//');
        $this->writeLineToOutput('//  TESTING SUMMARY');
        $this->writeLineToOutput('//');
        $this->writeLineToOutput(
            "// {$this->inSuccessFormat($resultSummary['passedTestCount'])} Passed, {$this->inErrorFormat($resultSummary['failedTestCount'])} Failed, {$this->inCommentFormat($resultSummary['skippedTestCount'])} Skipped"
        );
        $this->writeLineToOutput("// Completed in {$executionDurationText}");
        $this->writeBlankLineToOutput();

        foreach ($suiteMetricsList as $suiteMetrics) {
            if (count($suiteMetrics['failedTests']) === 0) {
                continue;
            }

            $this->writeLineToOutput("In {$suiteMetrics['suiteDescription']}:");
            foreach ($suiteMetrics['failedTests'] as $failedTestDescription => $failureException) {
                $this->registerFailedTest($failedTestDescription, $failureException);
                $this->outputTestFailureDetails($failureException);
                $this->writeBlankLineToOutput();
            }
        }

        $this->writeBlankLineToOutput();
    }
}
