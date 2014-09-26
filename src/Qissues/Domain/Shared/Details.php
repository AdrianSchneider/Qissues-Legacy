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
            if ($expectation->isRequired() and empty($this->details[$field])) {
                $this->violations[] = "Required field '$field' was missing";
                return false;
            }

            if ($options = $expectation->getOptions()) {
                if (!empty($this->details[$field]) and !$this->valueMatchesOptions($this->details[$field], $options)) {
                    $this->violations[] = "$field only accepts one of [" . implode(', ', $options) . "]";
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Fuzzy match in_array replacement
     *
     * @param string $value
     * @param array $options
     * @return boolean
     */
    protected function valueMatchesOptions($value, array $options)
    {
        foreach ($options as $option) {
            if (stripos((string)$option, (string)$value) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the violations from the last satisfy call
     * @return array
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
