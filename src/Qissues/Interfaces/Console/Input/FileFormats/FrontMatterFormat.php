<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Application\Input\FrontMatterParser;
use Symfony\Component\Yaml\Dumper;

class FrontMatterFormat implements FileFormat
{
    protected $parser;
    protected $ymlDumper;
    protected $contentField;

    /**
     * @param FrontMatterParser $parser
     * @param string $contentField
     */
    public function __construct(FrontMatterParser $parser, Dumper $ymlDumper, $contentField = 'description')
    {
        $this->parser = $parser;
        $this->ymlDumper = $ymlDumper;
        $this->contentField = $contentField;
    }

    /**
     * {@inheritDoc}
     */
    public function seed(ExpectedDetails $expectations)
    {
        return sprintf(
            "---\n%s\n---%s\n",
            $this->buildYml($expectations),
            $this->buildContent($expectations[$this->contentField])
        );
    }

    /**
     * Build the YLM portion
     *
     * @param ExpectedDetails $expectations
     * @return string YML
     */
    protected function buildYml(ExpectedDetails $expectations)
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

    protected function buildYmlPortion(array $fields, $expectations)
    {
        return $this->stripQuotedEmptyStrings(
            $this->addComments(
                $this->ymlDumper->dump($fields, 500),
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
     *
     * @param string $yml
     * @return string
     */
    protected function stripQuotedEmptyStrings($yml)
    {
        $replacements = array(
            "''\n" => "\n",
            "'' #" => ' #'
        );

        return trim(
            str_replace(
                array_keys($replacements),
                array_values($replacements),
                "\n$yml\n"
            ),
            "\n"
        );
    }

    /**
     * Build the content portion
     *
     * @param ExpectedDetails $expectations
     * @return string content
     */
    protected function buildContent(ExpectedDetail $expectation)
    {
        return "";
    }

    /**
     * {@inheritDoc}
     */
    public function parse($content)
    {
        return new Details($this->parser->parse($content, $this->contentField));
    }
}
