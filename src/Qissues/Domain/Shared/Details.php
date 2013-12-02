<?php

namespace Qissues\Domain\Shared;

class Details
{
    protected $details;
    protected $violations;

    public function __construct(array $details = array())
    {
        $this->details = $details;
    }

    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Check to see if these details satisfy expectations
     *
     * @param ExpectedDetails $expectations
     * @return boolean true if satisfies
     */
    public function satisfy(ExpectedDetails $expectations)
    {
        $this->violations = array();

        foreach ($expectations as $field => $expectation) {
            if (!isset($this->details[$field])) {
                $this->violations[] = "Required field '$field' was missing";
                return false;
            }

            if ($options = $expectation->getOptions()) {
                if (!in_array($this->details[$field], $options)) {
                    $this->violations[] = "$field only accepts one of [" . implode(', ', $options) . "]";
                    return false;
                }
            }
        }

        return true;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}
