<?php

namespace Dose\LaravelFeature\tests;

use Dose\Feature\Feature;
use Dose\LaravelFeature\FeatureConnector;
use Dose\LaravelFeature\LaravelFeature;

class LaravelFeatureTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $features;

    public function setUp()
    {
        $this->features = [
            'a' => 'b',
            'b' => 'c',
            'c' => 'd',
        ];
    }

    /** @test */
    public function it_should_return_a_variant()
    {
        $this->assertTrue($this->getLaravelFeature()->getVariant('a') == 'b');
    }

    /** @test */
    public function it_should_be_able_to_change_context()
    {
        $this->getLaravelFeature()->setContext('new_context');
        $this->assertTrue($this->context == 'new_context');
    }

    protected function getLaravelFeature()
    {
        return new LaravelFeature($this->getFeatureMock(), $this->getConnectorMock());
    }

    protected function getFeatureMock()
    {
        $mock = $this->getMockBuilder(Feature::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getVariant')->will($this->returnCallback(function ($name) {
            return $this->features[$name];
        }));

        return $mock;
    }

    protected function getConnectorMock()
    {
        $mock = $this->getMockBuilder(FeatureConnector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('setContext')->will($this->returnCallback(function ($context) {
            $this->context = $context;
        }));

        return $mock;
    }
}
