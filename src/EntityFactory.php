<?php

namespace Dose\LaravelFeature;

use Dose\Feature\Entity;
use Dose\Feature\Roller;

/**
 * Entity factory.
 *
 * @package Dose\LaravelFeature
 */
class EntityFactory
{
    /**
     * @var Roller
     */
    protected $roller;

    /**
     * @param Roller $roller
     */
    public function __construct(Roller $roller)
    {
        $this->roller = $roller;
    }

    /**
     * Creates an instance of Config.
     *
     * @return Entity
     */
    public function make()
    {
        return new Entity($this->roller);
    }
}
