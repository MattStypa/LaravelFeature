<?php

namespace Dose\LaravelFeature;

use Dose\Feature\ConnectorInterface;
use Illuminate\Config\Repository as LaravelConfig;
use Illuminate\Http\Request;
use Illuminate\Session\Store as SessionStore;

/**
 * Provide externally available configuration points.
 *
 * @package Dose\LaravelFeature
 */
class FeatureConnector implements ConnectorInterface
{
    /**
     * URL parameter used to specify overrides.
     */
    const OVERRIDE_KEY = 'features';

    /**
     * Separates multiple overrides.
     */
    const OVERRIDE_DELIMITER = ',';

    /**
     * Separates feature and variant.
     */
    const OVERRIDE_SETTER = ':';

    /**
     * Package namespace.
     */
    const PACKAGE_CONFIG_NAMESPACE = 'laravelfeature';

    /**
     * Name of the config path to features.
     */
    const FEATURE_CONFIG_KEY = 'features';

    /**
     * Allow override config key.
     */
    const ALLOW_OVERRIDE_CONFIG_KEY = 'allow_override';

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var LaravelConfig
     */
    protected $laravelConfig;

    /**
     * @var string Current context
     */
    protected $context;

    /**
     * @var array Keeps track of overrides.
     */
    protected $overrides;

    /**
     * @param EntityFactory $entityFactory
     * @param SessionStore $session
     * @param LaravelConfig $laravelConfig
     * @param Request $request
     */
    public function __construct(EntityFactory $entityFactory, SessionStore $session, LaravelConfig $laravelConfig, Request $request)
    {
        $this->entityFactory = $entityFactory;
        $this->context = $session->getId();
        $this->laravelConfig = $laravelConfig;

        if ($overrides = $request->input(self::OVERRIDE_KEY)) {
            foreach (explode(self::OVERRIDE_DELIMITER, $overrides) as $override) {
                $override = explode(self::OVERRIDE_SETTER, $override);
                if (count($override) != 2) {
                    continue;
                }
                list($feature, $variant) = $override;
                $this->overrides[$feature] = $variant;
            }
        }
    }

    /**
     * Gets a defined feature entity object.
     *
     * @param $name string
     * @return Entity
     */
    public function getEntity($name)
    {
        $variantOverride = $this->getOverride($name);

        if ($variantOverride !== null) {
            $entity = $this->entityFactory->make();
            $entity->addVariant($variantOverride, 100);

            return $entity;
        }

        $config = $this->getLaravelConfigValue([self::FEATURE_CONFIG_KEY, $name]);

        return $this->getEntityFromConfig($config);
    }

    /**
     * Gets an Entity object from configuration array.
     *
     * @param $config mixed
     * @return Entity
     */
    protected function getEntityFromConfig($config)
    {
        $entity = $this->entityFactory->make();

        if (is_array($config)) {
            foreach ($this->getVariants($config) as $variant => $odds) {
                $entity->addVariant($variant, $odds);
            }
        } else {
            $entity->addVariant(true, (float)$config);
        }

        return $entity;
    }

    /**
     * Sets environment context.
     *
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = (string)$context;
    }

    /**
     * Gets environment context.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns overridden variant if set and allowed.
     *
     * @param $name
     * @return string|null
     */
    protected function getOverride($name)
    {
        if ($this->getLaravelConfigValue(self::ALLOW_OVERRIDE_CONFIG_KEY)) {
            return array_get($this->overrides, $name, null);
        }

        return null;
    }

    /**
     * Gets sanitized variants from configuration.
     *
     * @param $config mixed
     * @return array
     */
    protected function getVariants($config)
    {
        $variants = [];
        $autoOddsVariants = [];
        $totalOdds = 0;

        foreach ($config as $key => $value) {
            if (!is_string($key)) {
                $autoOddsVariants[] = $value;
            } else {
                $value = $this->getSanitizedOdds($value);
                $variants[$key] = $value;
                $totalOdds += $value;
            }
        }

        if ($autoOddsVariants) {
            $autoOdds = (100 - $totalOdds) / count($autoOddsVariants);

            foreach ($autoOddsVariants as $variant) {
                $variants[$variant] = $autoOdds;
            }

            // Ensure complete coverage
            end($variants);
            $variants[key($variants)] = 100;
        }

        return $variants;
    }

    /**
     * Gets odds after ensuring that the value is positive.
     *
     * @param $odds float
     * @return float
     */
    protected function getSanitizedOdds($odds)
    {
        $odds = (float)$odds;

        return $odds < 0 ? 0 : $odds;
    }

    /**
     * Retrieves a configuration value from Laravel.
     *
     * @param $option
     * @return mixed
     */
    protected function getLaravelConfigValue($option)
    {
        $option = implode('.', (array)$option);

        // Laravel 5
        $optionKey = self::PACKAGE_CONFIG_NAMESPACE . '.' . $option;
        if ($this->laravelConfig->has($optionKey)) {
            return $this->laravelConfig->get($optionKey);
        }

        // Laravel 4
        $optionKey = self::PACKAGE_CONFIG_NAMESPACE . '::' . $option;

        return $this->laravelConfig->get($optionKey);
    }
}
