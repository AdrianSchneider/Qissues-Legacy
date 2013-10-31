<?php

namespace Qissues\Console\Input\FileFormats;

use Qissues\System\FrontMatterParser;

class FrontMatterFormat implements FileFormat
{
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
