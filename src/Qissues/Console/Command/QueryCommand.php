<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class QueryCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('List all issues, optionally filtering them.')

            // display
            ->addOption('size', 'z', InputOption::VALUE_OPTIONAL, 'View mode (tiny, basic or detailed) defaults based on width)', null)
            ->addOption('web', 'w', InputOption::VALUE_NONE, 'Open in web browser.', null)
            ->addOption('report', 'r', InputOption::VALUE_OPTIONAL, 'Load a report from configuration', null)

            // criteria
            ->addOption('keyword', 'k', InputOption::VALUE_OPTIONAL, 'Query by keyword', null)
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter by status', array())
            ->addOption('assignee', 'a', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter by assignee', null)
            ->addOption('priority', 'p', InputOption::VALUE_OPTIONAL, 'Filter by priority', null)
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter by type', array())
            ->addOption('mine', null, InputOption::VALUE_NONE, 'Only show things assigned to me', null)
            ->addOption('ids', 'i', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter by IDs', null)
            ->addOption('labels', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter by labels', null)

            // sorting or limiting
            ->addOption('sort', 'o', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Sort results by [priority]', array('updated'))
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit the results', 50)
            ->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Jump to results page', 1)
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker = $this->getApplication()->getTracker();
        $repository = $tracker->getRepository();

        if ($input->getOption('web')) {
            $this->get('console.output.browser')->open($repository->getUrl());
            return 0;
        }

        if ($report = $input->getOption('report')) {
            $reports = $this->getParameter('reports');
            if (empty($reports[$report])) {
                $output->writeln("<error>Invalid report</error>");
                return 1;
            }
            $criteria = $this->get('console.input.report_criteria_builder')->build($reports[$report]);
        } else {
            $criteria = $this->get('console.input.criteria_builder')->build($input);
        }


        if (!$issues = $repository->query($criteria)) {
            $output->writeln("<info>No issues found!</info>");
            return 0;
        }

        list($width, $height) = $this->getApplication()->getTerminalDimensions();
        if (!$size = $input->getOption('size')) {
            if ($width > 150) {
                $size = 'detailed';
            } elseif ($width > 100) {
                $size = 'basic';
            } else {
                $size = 'tiny';
            }
        }

        $view = $this->get('console.output.issues_views.' . $size);
        return $output->writeln($view->render($issues, $tracker->getFeatures(), $width, $height));
    }
}
