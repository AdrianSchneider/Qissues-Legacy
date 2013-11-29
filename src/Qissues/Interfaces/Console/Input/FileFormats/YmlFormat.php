<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Application\Input\Field;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class YmlFormat implements FileFormat
{
    protected $parser;
    protected $dumper;
    protected $depth;

    /**
     * @param Parser $parser yaml parser
     * @param Dumper $dumper yaml dumper
     */
    public function __construct(Parser $parser, Dumper $dumper, $depth = 100)
    {
        $this->parser = $parser;
        $this->dumper = $dumper;
        $this->depth = $depth;
    }

    /**
     * Use YML to seed the file
     *
     * {@inheritDoc}
     */
    public function seed(array $fields)
    {
        $out = '';
        foreach ($fields as $i => $field) {
            if ($field instanceof Field and $options = $field->getOptions()) {
                $out .= "# $field: [" . implode(', ', $options) . "]\n";
                unset($fields[$i]);
                $fields["$field"] = $field->getDefault() ?: '';
            }
        }

        $out .=  str_replace("''\n", "\n", $this->dumper->dump($fields, $this->depth));

        return $out;
    }

    /**
     * Use YLM to parse the file
     *
     * {@inheritDoc}
     */
    public function parse($input)
    {
        return $this->parser->parse($input);
    }
}
