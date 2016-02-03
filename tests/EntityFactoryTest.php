<?php

namespace Dose\LaravelFeature\tests;

use Dose\Feature\Entity;
use Dose\Feature\Roller;
use Dose\LaravelFeature\EntityFactory;

class EntityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_return_config_object()
    {
        $this->assertTrue($this->getConfigFactory()->make() instanceof Entity);
    }

    protected function getConfigFactory()
    {
        return new EntityFactory($this->getRollerMock());
    }

    protected function getRollerMock()
    {
        return $this->getMockBuilder(Roller::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
