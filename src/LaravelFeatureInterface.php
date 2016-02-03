<?php

namespace Dose\LaravelFeature;

/**
 * Main class for the LaravalFeature package.
 *
 * @package Dose\LaravelFeature
 */
interface LaravelFeatureInterface
{
    /**
     * Gets winning variant.
     *
     * @param string $name
     * @return string
     */
    public function getVariant($name);

    /**
     * Sets context.
     *
     * @param string $context
     */
    public function setContext($context);
}
