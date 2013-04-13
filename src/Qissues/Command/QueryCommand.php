<?php

namespace Qissues\Command;

use Qissues\Connector\BitBucket;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class QueryCommand extends Command
{
    protected $priorities = array(
        5 => '▲',
        4 => '▴',
        3 => '-',
        2 => '▾',
        1 => '▼'
    );
    protected $types = array(
        'bug' => '<p5>B</p5>'
    );

    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('List all issues, optionally filtering them.')
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, 'Filter by status', null)
            ->addOption('sort', 'o', InputOption::VALUE_OPTIONAL, 'Sort results by [priority]', null)
            ->addOption('assignee', 'a', InputOption::VALUE_OPTIONAL, 'Filter by assignee', null)
            ->addOption('priority', 'p', InputOption::VALUE_OPTIONAL, 'Filter by priority', null)
            ->addOption('kind', 'k', InputOption::VALUE_OPTIONAL, 'Filter by kind', null)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($width, $height) = $this->getApplication()->getTerminalDimensions();

        $config = $this->getApplication()->getConfig();
        $repo = new BitBucket($config['bitbucket']);
        $issues = $repo->findAll($this->buildOptions($input));

        $maxLength = 0;
        foreach ($issues as $issue) {
            if (strlen($issue['local_id']) > $maxLength) {
                $maxLength = strlen($issue['local_id']);
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
                $this->priorities[$issue['priority']],
                $issue['metadata']['kind'] == 'bug' ? $this->types['bug'] : ' ',
                str_repeat(' ', $maxLength - strlen($issue['local_id'])),
                $issue['local_id'],
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

        $searchFor = array('sort', 'assignee', 'priority', 'kind');
        foreach ($searchFor as $field) {
            if ($value = $input->getoption($field)) {
                $options[$field] = $value;
            }
        }

        return $options;
    }
}
