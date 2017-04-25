<?php

namespace Kase;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;

/**
 * Symfony component console command which defines the main Kase 'run' command usable from the
 * command line
 *
 * @package Kase
 */
class RunKaseTestsCommand extends Command
{
    private $config;

    public function __construct(array $config = null)
    {
        $this->config = $config;
        parent::__construct();
    }

    /**
     * Defines the command name and available options
     */
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Kase testing framework CLI runner')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'The config file used to set up Kase before running tests',
                __DIR__.DIRECTORY_SEPARATOR.'kase-config.php' // default value
            );
    }

    /**
     * @param  InputInterface  $input  the input interface to use when executing the command
     * @param  OutputInterface $output the output interface to use when executing the command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // INCLUDE KASE BOOTSTRAP
        if (isset($this->config) === false) {
            $configPath = $input->getOption('config');
            if ($configPath && file_exists($configPath)) {
                $this->config = require $configPath;
            } else {
                $output->writeln("Error: Could not find specified Kase config file: {$configPath}\n\n");
                return;
            }
        }


        // VERIFY REQUIRED RESOURCES ARE DEFINED IN CONFIG FILE
        if (array_key_exists('testSuitePathProvider', $this->config) === false || is_callable($this->config['testSuitePathProvider']) === false) {
            $output->writeln('Error: Required "testSuitePathProvider" callable not found in config');
            return;
        }

        // SET UP TESTING RESOURCES
        $metricsLog = [];
        $testingResources = [
            'validator'     => (isset($this->config['validator']) ? $this->config['validator'] : new TestValidator()),
            'reporter'      => (isset($this->config['reporter']) ? $this->config['reporter'] : new DefaultKaseCLIReporter($output)),
            'metricsLogger' => function ($metricsToRecord) use (&$metricsLog) {
                $metricsLog[] = $metricsToRecord;
            },
            'console'      => $output // normally shouldn't be used in testing, mostly for unit testing of Kase
        ];

        // SEND RUNNER INITIALIZATION EVENT TO REPORTER
        $testingResources['reporter']->registerTestRunnerInitialization();

        // RUN TESTS
        $suiteFileProvider = $this->config['testSuitePathProvider'];
        foreach ($suiteFileProvider() as $testSuiteFilePath) {
            $suiteRunner = require $testSuiteFilePath;
            $suiteRunner($testingResources);
        }

        // REPORT RESULTS
        $testingResources['reporter']->registerSuiteMetricsSummary($metricsLog);
    }
}
