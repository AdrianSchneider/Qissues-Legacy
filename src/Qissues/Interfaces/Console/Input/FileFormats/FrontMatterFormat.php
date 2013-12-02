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
        $metaFields = $expectations->getDefaults();
        unset($metaFields[$this->contentField]);

        return $this->stripQuotedEmptyStrings(
            $this->addComments(
                $this->ymlDumper->dump($metaFields, 500),
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
