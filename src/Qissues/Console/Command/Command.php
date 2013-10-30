<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Command extends BaseCommand
{
    /**
     * Get the requested ID
     *
     * Attempts to grab from git branch if ommitted
     *
     * @param InputInterface input from console
     * @return integer|null the id if found
     */
    protected function getIssueId(InputInterface $input)
    {
        if ($id = $input->getArgument('issue')) {
            return $id;
        }

        if (is_dir('./.git')) {
            $branch = trim(shell_exec('git rev-parse --symbolic-full-name --abbrev-ref HEAD'));
            $matches = null;
            if (preg_match('/^(.*)-([0-9]+)$/', $branch, $matches)) {
                return $matches[2];
            }
        }
    }
}
