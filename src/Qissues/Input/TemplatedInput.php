<?php

namespace Qissues\Input;

use Symfony\Component\Yaml\Parser;

class TemplatedInput
{
    protected $ymlParser;

    public function __construct(Parser $ymlParser)
    {
        $this->ymlParser = $ymlParser;
    }

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

    protected function split($content)
    {
        $parts = array_map('trim', explode('---', $content, 2));
        if (count($parts) < 2) {
            throw new \InvalidArgumentException('$content requires YML, ---, then body');
        }

        return $parts;
    }
}
