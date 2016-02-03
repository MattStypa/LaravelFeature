<?php

namespace Dose\LaravelFeature;

use Dose\Feature\ConnectorInterface;
use Dose\Feature\Feature;

/**
 * Main class for the LaravalFeature package.
 *
 * @package Dose\LaravelFeature
 */
class LaravelFeature implements LaravelFeatureInterface
{
    /**
     * @var Feature
     */
    protected $feature;

    /**
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * LaravelFeature constructor.
     *
     * @param Feature $feature
     * @param ConnectorInterface $connector
     */
    public function __construct(Feature $feature, ConnectorInterface $connector)
    {
        $this->feature = $feature;
        $this->connector = $connector;
    }

    /**
     * Gets winning variant.
     *
     * @param string $name
     * @return string
     */
    public function getVariant($name)
    {
        return $this->feature->getVariant($name);
    }

    /**
     * Sets context.
     *
     * @param string $context
     */
    public function setContext($context)
    {
        $this->connector->setContext($context);
    }
}
