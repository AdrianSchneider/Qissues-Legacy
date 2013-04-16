<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class CreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new issue')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector('BitBucket');

        $issue = $this->getIssueDetailsFromExternal();
        $issue = $connector->create($issue);

        if ($issue['title'] == 'Title') {
            return $output->writeln('<error>No changes were made</error>');
        }

        $output->writeln("Issue <info>#$issue[id]</info> has been created");
    }

    protected function getIssueDetailsFromExternal()
    {
        $config = $this->getApplication()->getConfig();
        $me = $config['bitbucket']['username'];

        $filename = tempnam('.', 'qissues');
        file_put_contents($filename, "Title\n\nPriority: minor\nType: bug\nAssignee: $me\n\nDescription\n");
        exec("vim $filename > `tty`");
        $data = file_get_contents($filename);
        unlink($filename);
        $lines = explode("\n", $data);

        return array(
            'title' => trim($lines[0]),
            'priority' => trim(str_replace('Priority:', '', $lines[2])),
            'type' => trim(str_replace('Type:', '', $lines[3])),
            'assignee' => trim(str_replace('Assignee:', '', $lines[4])),
            'description' => trim(implode("\n", array_slice($lines, 5)))
        );
    }
}
