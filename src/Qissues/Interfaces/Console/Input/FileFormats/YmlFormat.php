<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetails;
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
    public function seed(ExpectedDetails $expectations)
    {
        return $this->stripQuotedEmptyStrings(
            $this->addComments(
                $this->dumper->dump($expectations->getDefaults(), $this->depth),
                $expectations
            )
        );
    }

    /**
     * Add comments above each field if there are options
     *
     * @param string $yml
     * @param ExpectedDetails $expectations
     */
    protected function addComments($yml, ExpectedDetails $expectations)
    {
        $yml = "\n$yml";
        foreach ($expectations as $field => $expectation) {
            if ($options = $expectation->getOptions()) {
                $yml = str_replace(
                    "\n$field: ",
                    "\n# [" . implode(', ', $options) . "]\n$field: ",
                    $yml
                );
            }
        }

        return trim($yml);
    }

    /**
     * Removes quotes around empty strings
     * emptyField: ""\n turns to emptyField: \n
     *
     * @param string $yml
     * @return string
     */
    protected function stripQuotedEmptyStrings($yml)
    {
        return trim(str_replace("''\n", "\n", "$yml\n"), "\n");
    }

    /**
     * Use YLM to parse the file
     *
     * {@inheritDoc}
     */
    public function parse($input)
    {
        return new Details($this->parser->parse($input));
    }
}
