<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
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
            ->addOption('view', 'z', InputOption::VALUE_OPTIONAL, 'View mode (tiny, basic or detailed) defaults based on width)', null)
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, 'Filter by status', "new,open")
            ->addOption('sort', 'o', InputOption::VALUE_OPTIONAL, 'Sort results by [priority]', null)
            ->addOption('assignee', 'a', InputOption::VALUE_OPTIONAL, 'Filter by assignee', null)
            ->addOption('priority', 'p', InputOption::VALUE_OPTIONAL, 'Filter by priority', null)
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Filter by type', null)
            ->addOption('mine', null, InputOption::VALUE_NONE, 'Only show things assigned to me', null)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit the results', 50)
            ->addOption('web', 'w', InputOption::VALUE_NONE, 'Open in web browser.', null)
        ;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector();

        if ($input->getOption('web')) {
            return exec(sprintf(
                'xdg-open %s',
                escapeshellarg($connector->getBrowseUrl())
            ));
        }


        list($width, $height) = $this->getApplication()->getTerminalDimensions();
        $issues = $connector->findAll($this->buildOptions($input));

        if (!$view = $input->getOption('view')) {
            if ($width > 150) {
                $view = 'detailed';
            } elseif ($width > 100) {
                $view = 'basic';
            } else {
                $view = 'tiny';
            }
        }

        if ($view == 'detailed') {
            return $this->renderDetailedView($issues, $output);
        }

        if ($view == 'basic') {
            return $this->renderBasicView($issues, $output);
        }

        return $this->renderTinyView($issues, $output);
    }

    /**
     * Renders a detailed table view of the issues
     *
     * @param array issues from connector
     * @param OutputInterface
     */
    protected function renderDetailedView(array $issues, OutputInterface $output)
    {
        list($width, $height) = $this->getApplication()->getTerminalDimensions();

        $renderIssues = array();
        foreach ($issues as $issue) {
            $renderIssues[] = array(
                '#'            => $issue['id'],
                'Title'        => strlen($issue['title']) > $width * 0.4 
                    ? (substr($issue['title'], 0, $width * 0.4) . '...')
                    : $issue['title'],
                'Status'       => $issue['status'],
                'Type'         => $issue['type'],
                'Priority'     => $issue['priority_text'],
                'Assignee'     => $issue['assignee'],
                'Date Created' => $issue['created']->format('Y-m-d g:ia'),
                'Date updated' => $issue['updated']->format('Y-m-d g:ia'),
                'Comments'     => $issue['comments']
            );
        }

        $renderer = new \Qissues\Renderer\TableRenderer();
        $output->writeln($renderer->render($renderIssues, $width));
    }

    /**
     * Renders a basic view of the issues
     *
     * @param array issues from connector
     * @param OutputInterface
     */
    protected function renderBasicView(array $issues, OutputInterface $output)
    {
        list($width, $height) = $this->getApplication()->getTerminalDimensions();

        $renderIssues = array();
        foreach ($issues as $issue) {
            $renderIssues[] = array(
                'Id'           => $issue['id'],
                'Title'        => strlen($issue['title']) > $width * 0.4 
                    ? (substr($issue['title'], 0, $width * 0.4) . '...')
                    : $issue['title'],
                'Status'       => $issue['status'],
                'Type'         => $issue['type'],
                'P'            => $issue['priority'],
                'Date updated' => $issue['updated']->format('Y-m-d g:ia')
            );
        }

        $renderer = new \Qissues\Renderer\TableRenderer();
        $output->writeln($renderer->render($renderIssues, $width));
    }

    /**
     * Renders a tiny view for sidebars (ex in tmux)
     *
     * @param array issues from connector
     * @param OutputInterface
     */
    protected function renderTinyView(array $issues, OutputInterface $output)
    {
        list($width, $height) = $this->getApplication()->getTerminalDimensions();

        $priorities = array(
            5 => '▲',
            4 => '▴',
            3 => '-',
            2 => '▾',
            1 => '▼'
        );
        $types = array(
            'bug' => '<p5>B</p5>'
        );

        $maxLength = 0;
        foreach ($issues as $issue) {
            if (strlen($issue['id']) > $maxLength) {
                $maxLength = strlen($issue['id']);
            }
        }

        $allowedSize = $width
            - 4          // icons
            - $maxLength // number area
            - 1          // space
        ;

        foreach ($issues as $issue) {
            $output->writeln(sprintf(
                '%s %s <comment>%s%d</comment> <message>%s</message>',
                $priorities[$issue['priority']],
                $issue['type'] == 'bug' ? $types['bug'] : ' ',
                str_repeat(' ', $maxLength - strlen($issue['id'])),
                $issue['id'],
                strlen($issue['title']) > $allowedSize
                    ? (substr($issue['title'], 0, $allowedSize - 3) . '...')
                    : $issue['title']
            ));
        }
    }

    /**
     * Converts the Input into something our connector can understand
     * TODO convert to Filter objecft
     *
     * @param InputInterface
     * @return array of options
     */
    protected function buildOptions($input)
    {
        $options = array();

        $searchFor = array('sort', 'assignee', 'priority', 'type', 'status', 'limit');
        foreach ($searchFor as $field) {
            if ($value = $input->getOption($field)) {
                $options[$field] = $value;
            }
        }

        if ($input->getOption('mine')) {
            $config = $this->getApplication()->getConfig();
            $options['assignee'] = $config[strtolower($config['connector'])]['username'];
        }

        return $options;
    }
}
