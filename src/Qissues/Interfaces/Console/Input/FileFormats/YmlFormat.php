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
        $requiredPairs = array();
        $optionalPairs = array();

        foreach ($expectations as $field) {
            if ($field->getName() == 'description') {
                continue;
            }

            if ($field->isRequired()) {
                $requiredPairs[$field->getName()] = $field->getDefault();
            } else {
                $optionalPairs[$field->getName()] = $field->getDefault();
            }
        }

        $out = '';
        if ($requiredPairs) {
            $out .= $this->buildYmlPortion($requiredPairs, $expectations);
        }
        if ($optionalPairs) {
            $out .= "\n\n# Optional Fields\n";
            $out .= $this->buildYmlPortion($optionalPairs, $expectations);
        }

        return $out;
    }

    /**
     * Build the YML from fields
     * @param array $fields
     * @param ExpectedDetails $expectations
     * @return string yml
     */
    protected function buildYmlPortion(array $fields, $expectations)
    {
        return $this->stripQuotedEmptyStrings(
            $this->addComments(
                $this->dumper->dump($fields, 500),
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
        $yml = "\n$yml\n";

        foreach ($expectations as $field => $expectation) {
            if ($options = $expectation->getOptions()) {
                $matches = null;
                if (preg_match("/\n$field\:(.*)$/im", $yml, $matches, \PREG_OFFSET_CAPTURE)) {
                    $before = substr($yml, 0, $matches[0][1] + strlen($matches[0][0]));
                    $after = substr($yml, $matches[1][1] + strlen($matches[1][0]));

                    $comment = ' # [' . implode(', ', $options) . ']';
                    $yml = trim($before, "\n") . $comment . $after;
                }
            }
        }

        return trim($yml, "\n");
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
