<?php

namespace Dose\LaravelFeature\tests;

use Dose\Feature\Entity;
use Dose\LaravelFeature\EntityFactory;
use Dose\LaravelFeature\FeatureConnector;
use Illuminate\Config\Repository as LaravelConfig;
use Illuminate\Http\Request;
use Illuminate\Session\Store as SessionStore;

class FeatureConnectorTest extends \PHPUnit_Framework_TestCase
{
    protected $entity;
    protected $sessionId;
    protected $config;
    protected $variants;
    protected $input;

    public function setUp()
    {
        $this->config['allow_override'] = false;
        $this->entity = $this->getEntityMock();
    }

    /** @test */
    public function it_should_return_session_id_as_context()
    {
        $this->sessionId = 'test_context';
        $this->assertTrue($this->getConnector()->getContext() === 'test_context');
    }

    /** @test */
    public function is_should_allow_for_context_change()
    {
        $connector = $this->getConnector();
        $connector->setContext('new_context');
        $this->assertTrue($connector->getContext() === 'new_context');
    }

    /** @test */
    public function it_should_parse_config_with_just_odds()
    {
        $this->config['features.test'] = 20;

        $this->entity->expects($this->once())
            ->method('addVariant')
            ->with(true, 20);

        $this->getConnector()->getEntity('test');
    }

    /** @test */
    public function it_should_parse_config_with_multiple_variants()
    {
        $this->config['features.test'] = [
            'variant_one',
            'variant_two',
            'variant_three',
            'variant_four',
        ];

        $this->entity->expects($this->exactly(4))
            ->method('addVariant')
            ->withConsecutive(
                ['variant_one', 25],
                ['variant_two', 25],
                ['variant_three', 25],
                ['variant_four', 100] // Last autoscaled variant is always 100
            );

        $this->getConnector()->getEntity('test');
    }

    /** @test */
    public function it_should_parse_config_with_multiple_variants_incomplete_coverage()
    {
        $this->config['features.test'] = [
            'variant_one' => 10,
            'variant_two' => 10,
            'variant_three' => 10,
            'variant_four' => 10,
        ];

        $this->entity->expects($this->exactly(4))
            ->method('addVariant')
            ->withConsecutive(
                ['variant_one', 10],
                ['variant_two', 10],
                ['variant_three', 10],
                ['variant_four', 10]
            );

        $this->getConnector()->getEntity('test');
    }

    /** @test */
    public function it_should_parse_config_with_multiple_variants_auto_scale()
    {
        $this->config['features.test'] = [
            'variant_one' => 10,
            'variant_two',
            'variant_three',
            'variant_four' => 10,
        ];

        $this->entity->expects($this->exactly(4))
            ->method('addVariant')
            ->withConsecutive(
                ['variant_one', 10],
                ['variant_four', 10],
                ['variant_two', 40],
                ['variant_three', 100] // Last autoscaled variant is always 100
            );

        $this->getConnector()->getEntity('test');
    }

    /** @test */
    public function it_should_override_variants_if_allowed()
    {
        $this->config['allow_override'] = true;
        $this->config['features.test'] = [
            'variant_one' => 40,
            'variant_two' => 40,
            'variant_three' => 20,
            'variant_four' => 0,
        ];
        $this->input['features'] = 'test:variant_four';

        $this->entity->expects($this->once())
            ->method('addVariant')
            ->with('variant_four', 100);

        $this->getConnector()->getEntity('test');
    }

    /** @test */
    public function it_should_override_variants_when_variant_evaluates_to_false()
    {
        $this->config['allow_override'] = true;
        $this->config['features.test'] = [
            '0' => 40,
            '1' => 40,
            '2' => 20,
            '3' => 0,
        ];
        $this->input['features'] = 'test:0';

        $this->entity->expects($this->once())
            ->method('addVariant')
            ->with('0', 100);

        $this->getConnector()->getEntity('test');
    }

    /** @test */
    public function it_should_ignore_invalid_url_override()
    {
        $this->config['allow_override'] = true;
        $this->config['features.test'] = [
            'variant_one' => 40,
            'variant_two' => 40,
            'variant_three' => 20,
            'variant_four' => 0,
        ];
        $this->input['features'] = 'test';

        $this->entity->expects($this->exactly(4))
            ->method('addVariant')
            ->withConsecutive(
                ['variant_one', 40],
                ['variant_two', 40],
                ['variant_three', 20],
                ['variant_four', 0]
            );

        $this->getConnector()->getEntity('test');
    }

    /** @test */
    public function it_should_not_override_variants_if_not_allowed()
    {
        $this->config['allow_override'] = false;
        $this->config['features.test'] = [
            'variant_one' => 40,
            'variant_two' => 40,
            'variant_three' => 20,
            'variant_four' => 0,
        ];
        $this->input['features'] = 'test:variant_four';

        $this->entity->expects($this->exactly(4))
            ->method('addVariant')
            ->withConsecutive(
                ['variant_one', 40],
                ['variant_two', 40],
                ['variant_three', 20],
                ['variant_four', 0]
            );

        $this->getConnector()->getEntity('test');
    }

    protected function getConnector()
    {
        return new FeatureConnector(
            $this->getEntityFactoryMock(),
            $this->getSessionMock(),
            $this->getLaravelConfigMock(),
            $this->getRequestMock()
        );
    }

    protected function getEntityFactoryMock()
    {
        $mock = $this->getMockBuilder(EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('make')->willReturn($this->entity);

        return $mock;
    }

    protected function getEntityMock()
    {
        $mock = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getSessionMock()
    {
        $mock = $this->getMockBuilder(SessionStore::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getId')->will($this->returnCallback(function () {
            return $this->sessionId;
        }));

        return $mock;
    }

    protected function getLaravelConfigMock()
    {
        $mock = $this->getMockBuilder(LaravelConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('get')->will($this->returnCallback(function ($feature) {
            $feature = preg_replace('/^.+?::/', '', $feature);

            return $this->config[$feature];
        }));

        return $mock;
    }

    protected function getRequestMock()
    {
        $mock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Request has it's own method called method
        $mock->expects($this->any())->method('input')->will($this->returnCallback(function ($name) {
            return $this->input[$name];
        }));

        return $mock;
    }
}
