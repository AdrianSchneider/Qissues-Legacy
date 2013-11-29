<?php

namespace Qissues\Interfaces\Console\Output\View\IssuesList;

use Qissues\Trackers\Shared\Support\FeatureSet;
use Qissues\Interfaces\Console\Output\Serializer\IssueSerializer;

class JsonView
{
    protected $issueSerializer;

    public function __construct(IssueSerializer $issueSerializer)
    {
        $this->issueSerializer = $issueSerializer;

    }

    /**
     * Renders as serialized JSON
     * @param Issue[] $issues
     * @param FeatureSet $features
     * @param integer $width
     * @param integer $height
     * @return string json
     */
    public function render(array $issues, FeatureSet $features, $width, $height)
    {
        return json_encode(array_map(array($this->issueSerializer, 'serialize'), $issues));
    }
}
