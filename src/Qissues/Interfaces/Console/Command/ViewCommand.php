<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\IssueTracker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ViewCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('view')
            ->setDescription('View details for an issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
            ->addOption('no-comments', null, InputOption::VALUE_NONE, 'Don\'t print comments', null)
            ->addOption('web', 'w', InputOption::VALUE_NONE, 'Open in web browser.', null)
            ->addOption('view', 'z', InputOption::VALUE_OPTIONAL, 'Change view', 'standard')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker = $this->getApplication()->getTracker()->getRepository();
        $number = new Number($this->get('console.input.git_id')->getId($input));
        if (!$issue = $tracker->lookup($number)) {
            $output->writeln('<error>Issue not found.</error>');
            return 1;
        }

        if ($input->getOption('web')) {
            $this->get('console.output.browser')->open($tracker->lookupUrl($number));
            return 0;
        }

        list($width, $height) = $this->getApplication()->getTerminalDimensions();
        $comments = $input->getOption('no-comments') ? array() : $tracker->findComments($number);

        $view = $this->get(sprintf('console.output.view.issue.%s', $input->getOption('view')));
        $rendered = $view->render($issue, $width, $height, $comments);

        $output->writeln($rendered);
    }
}
