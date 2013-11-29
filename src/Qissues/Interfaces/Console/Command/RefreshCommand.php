<?php

namespace Qissues\Interfaces\Console\Command;

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
        $storage = $this->get('system.storage');
        $repository = $this->getApplication()->getTracker()->getRepository();

        $storage->set(
            sprintf( '%s-%s', $this->getParameter('tracker'), getcwd()),
            $repository->fetchMetadata()
        );

        $output->writeln(sprintf(
            "<info>%s</info> metadata has been updated!",
            ucfirst($this->getParameter('tracker'))
        ));
    }
}
