<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Transition;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetails;
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
     * @param ExpectedDetails $requirements
     * @return TransitionDetails
     */
    public function create(ExpectedDetails $requirements)
    {
        if (!count($requirements)) {
            return new Details(array());
        }

        if (!$content = trim($this->editor->getEdited($this->fileFormat->seed($requirements)))) {
            return new Details(array());
        }

        return $this->fileFormat->parse($content);
    }
}
