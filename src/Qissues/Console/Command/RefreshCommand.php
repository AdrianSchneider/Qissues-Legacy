<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class RefreshCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('refresh')
            ->setDescription('Refreshes metadata from tracker')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getApplication()->getTracker()->getRepository();
        $repository->refreshMetadata();

        $output->writeln("Metadata updated");
    }
}
