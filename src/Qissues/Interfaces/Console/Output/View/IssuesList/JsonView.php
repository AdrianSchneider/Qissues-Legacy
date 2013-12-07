<?php

namespace Qissues\Interfaces\Console\Output\View\IssuesList;

use Qissues\Domain\Model\Response\Issues;
use Qissues\Application\Tracker\Support\FeatureSet;
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
     * @param Issues $issues
     * @param FeatureSet $features
     * @param integer $width
     * @param integer $height
     * @return string json
     */
    public function render(Issues $issues, FeatureSet $features, $width, $height)
    {
        return json_encode(array_map(
            array($this->issueSerializer, 'serialize'), 
            iterator_to_array($issues)
        ));
    }
}
