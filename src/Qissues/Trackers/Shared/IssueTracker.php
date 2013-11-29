<?php

namespace Qissues\Trackers\Shared;

use Qissues\Domain\Workflow\Workflow;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Trackers\Shared\Support\Feature;
use Qissues\Trackers\Shared\Support\FeatureSet;

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
