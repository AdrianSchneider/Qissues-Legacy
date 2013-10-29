<?php

namespace Qissues\Command;

use Qissues\Connector\Connector;
use Qissues\Input\TemplatedInput;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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

        $issue = $this->getIssueDetailsFromExternal($connector);
        $issue = $connector->create($issue);

        if ($issue['title'] == 'Title' or !$issue['title']) {
            return $output->writeln('<error>No changes were made</error>');
        }

        $output->writeln("Issue <info>#$issue[id]</info> has been created");
    }

    /**
     * Fetch input from user input
     * @param Connector $connector
     * @return array issue details
     */
    protected function getIssueDetailsFromExternal(Connector $connector)
    {
        $template = '';
        foreach ($connector->getEditorFields() as $key => $value) {
            $template .= "$key: $value\n";
        }
        $template .= "---\nDescription";

        $input = new TemplatedInput(new Parser());
        return $input->parse($this->getFromEditor($template));
    }

    /**
     * Load template into temp file, open in editor, 
     * and return content once closed.
     *
     * @param string $template initial file contents
     * @return string user input
     */
    protected function getFromEditor($template)
    {
        $filename = tempnam('.', 'qissues');
        file_put_contents($filename, $template);
        
        $editor = getenv('EDITOR') ?: 'vim';
        exec("$editor $filename > `tty`");
        $content = file_get_contents($filename);

        unlink($filename);
        return $content;
    }
}
