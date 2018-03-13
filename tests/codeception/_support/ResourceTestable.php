<?php

namespace tests\codeception\_support;


/**
 * Class ResourceTestable
 * @package tests\codeception\_support
 */
class ResourceTestable
{
    /**
     * @var array
     */
    protected $resultCalls;

    /**
     * @inheritDoc
     */
    public function __call($name, $arguments)
    {
        $this->resultCalls[$name][] = $arguments;
    }

    public function getCalls($name)
    {
        return $this->resultCalls[$name];
    }

    public function getAllCalls()
    {
        return $this->resultCalls;
    }
}
