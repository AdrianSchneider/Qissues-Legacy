<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Transition;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\RequiredDetails;
use Qissues\Interfaces\Console\Input\ExternalFileEditor;
use Qissues\Interfaces\Console\Input\FileFormats\FileFormat;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditStrategy implements DetailsStrategy
{
    protected $editor;
    protected $fileFormat;

    public function __construct(ExternalFileEditor $editor, FileFormat $fileFormat)
    {
        $this->editor = $editor;
        $this->fileFormat = $fileFormat;
    }

    function init(InputInterface $input, OutputInterface $output, Application $application) { }

    /**
     * Creates a new TransitionDetails by loading the info into an editor
     *
     * @param RequiredDetails $requirements
     * @return TransitionDetails
     */
    public function create(RequiredDetails $requirements)
    {
        return new Details($this->getData($requirements));
    }


    protected function getData(RequiredDetails $requirements)
    {
        if (!$fields = $requirements->getFields()) {
            return array();
        }

        if (!$content = trim($this->editor->getEdited($this->fileFormat->seed($fields)))) {
            return array();
        }

        return $this->fileFormat->parse($content);
    }
}
