<?php

namespace Qissues\Model\Tracker;

use Qissues\Model\Tracker\Support\FeatureSet;

class IssueTracker
{
    public function __construct(IssueRepository $repository, FieldMapping $mapping, FeatureSet $features)
    {
        $this->repository = $repository;
        $this->mapping = $mapping;
        $this->features = $features;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getMapping()
    {
        return $this->mapping;
    }

    public function getFeatures()
    {
        return $this->features;
    }

    public function getSupport(Feature $feature)
    {
        return $this->features->getSupport($feature);
    }
}
