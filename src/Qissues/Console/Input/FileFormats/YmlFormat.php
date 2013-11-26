<?php

namespace Qissues\Console\Input\FileFormats;

use Qissues\Model\Meta\Field;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class YmlFormat implements FileFormat
{
    public function __construct(Parser $parser, Dumper $dumper)
    {
        $this->parser = $parser;
        $this->dumper = $dumper;
    }

    public function seed(array $fields)
    {
        // XXX

        $out = '';
        foreach ($fields as $i => $field) {
            if ($field instanceof Field and $options = $field->getOptions()) {
                $out .= "# $field: [" . implode(', ', $options) . "]\n";
                unset($fields[$i]);
                $fields["$field"] = $field->getDefault() ?: '';
            }
        }

        $out .=  str_replace("''\n", "\n", $this->dumper->dump($fields, 100));

        return $out;
    }

    public function parse($input)
    {
        return $this->parser->parse($input);
    }
}
