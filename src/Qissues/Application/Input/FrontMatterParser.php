<?php

namespace Qissues\Application\Input;

use Symfony\Component\Yaml\Parser;

class FrontMatterParser
{
    /**
     * @var Parser
     */
    protected $ymlParser;

    /**
     * @param Parser $ymlParser
     */
    public function __construct(Parser $ymlParser)
    {
        $this->ymlParser = $ymlParser;
    }

    /**
     * Parses a templated input file
     * @param string $content YML --- raw body
     * @param string $mainField description/body field name
     * @return array metadata + body as array
     */
    public function parse($content, $mainField = 'description')
    {
        list($metadata, $body) = $this->split($content);

        try {
            $info = $this->ymlParser->parse($metadata);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('$content metadata is not valid YLM: ' . $e->getMessage());
        }

        return $info + array($mainField => $body);
    }

    /**
     * Split the content into two parts, delimited by ---
     * @param string $content
     * @return array YML, body
     */
    protected function split($content)
    {
        $parts = array_map('trim', explode('---', $content, 3));
        if (count($parts) < 3) {
            throw new \InvalidArgumentException('$content requires ---YML--- body');
        }

        return array($parts[1], $parts[2]);
    }
}
