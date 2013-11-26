<?php

namespace Qissues\Model\Tracker;

use Qissues\Model\Workflow\Workflow;
use Qissues\Model\Tracker\Support\Feature;
use Qissues\Model\Tracker\Support\FeatureSet;

class IssueTracker
{
    protected $repository;
    protected $mapping;
    protected $features;
    protected $workflow;

    /**
     * @param IssueRepository $repository
     * @param FieldMapping $mapping
     * @param FeatureSet $features
     */
    public function __construct(IssueRepository $repository, FieldMapping $mapping, FeatureSet $features, Workflow $workflow)
    {
        $this->repository = $repository;
        $this->mapping = $mapping;
        $this->features = $features;
        $this->workflow = $workflow;
    }

    /**
     * @return IssueRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return FieldMapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @return FeatureSet
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @return Workflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }
}
