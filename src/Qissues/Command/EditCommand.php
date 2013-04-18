<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class EditCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('edit')
            ->setDescription('Edit an existing issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector('BitBucket');
        if (!$issue = $connector->find($this->getIssueId($input))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $changes = $this->getIssueDetailsFromExternal($issue);
        $connector->update($changes, $issue);

        $output->writeln("Issue <info>#$issue[id]</info> has been updated");
    }

    protected function getIssueDetailsFromExternal($existing)
    {
        $filename = tempnam('.', 'qissues');
        file_put_contents($filename, "$existing[title]\n\nPriority: $existing[priority_text]\nType: $existing[type]\nAssignee: $existing[assignee]\n\n$existing[description]\n");
        $editor = getenv('EDITOR') ?: 'vim';
        exec("$editor $filename > `tty`");
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
