<?php

namespace Qissues\Application\Tracker;

use Qissues\Domain\Workflow\Workflow;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Application\Tracker\Support\Feature;
use Qissues\Application\Tracker\Support\FeatureSet;

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
