<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Application\Input\FrontMatterParser;

class FrontMatterFormat implements FileFormat
{
    protected $parser;
    protected $contentField;

    public function __construct(FrontMatterParser $parser, $contentField = 'description')
    {
        $this->parser = $parser;
        $this->contentField = $contentField;
    }

    /**
     * {@inheritDoc}
     */
    public function seed(array $fields)
    {
        $content = 'Enter content here...';
        if (isset($fields[$this->contentField])) {
            $content = $fields[$this->contentField];
            unset($fields[$this->contentField]);
        }

        $template = "---\n";
        foreach ($fields as $key => $value) {
            $template .= "$key: $value\n";
        }
        $template .= "---\n$content";

        return $template;
    }

    /**
     * {@inheritDoc}
     */
    public function parse($content)
    {
        return $this->parser->parse($content, $this->contentField);
    }
}
