<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Acquisition;

use CrowdSec\Whm\Exception;

/**
 * The config class.
 * List all acquisition based on yaml server files.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
class Config
{
    public const COMMON_CONFIG = 'common';
    public const CONFIG_PATH = __DIR__ . '/acquisition.json';
    private $config;
    private $configsByType = [];
    private $mapConfig;
    private $mapNames;

    public function __construct(string $version)
    {
        $content = json_decode(file_get_contents(self::CONFIG_PATH), true);
        if (!isset($content[$version])) {
            throw new Exception("Version $version is not implemented");
        }
        $this->config = $content[$version];
    }

    public function getConfig(string $source = ''): array
    {
        return $source ? $this->config[$source] ?? [] : $this->config;
    }

    public function getConfigsByType(string $type): array
    {
        if (!isset($this->configsByType[$type])) {
            $result = [];
            foreach ($this->config as $source => $configs) {
                foreach ($configs as $configName => $configData) {
                    if ($configData['type'] === $type) {
                        $result[$source][] = $configName;
                    }
                }
            }

            $mapConfigs = $this->getMapConfigs();
            foreach ($mapConfigs as $source => $configs) {
                foreach ($configs as $name => $config) {
                    foreach ($config as $configName => $configData) {
                        if ($configData['type'] === $type) {
                            $result[$source][] = $name . '_' . $configName;
                        }
                    }
                }
            }

            $this->configsByType[$type] = $result;
        }

        return $this->configsByType[$type];
    }

    public function getMapNames(): array
    {
        if (null === $this->mapNames) {
            $result = [];
            foreach ($this->config as $configs) {
                foreach ($configs as $configName => $configData) {
                    if ('map' === $configData['type']) {
                        $result[] = $configName;
                    }
                }
            }

            $this->mapNames = $result;
        }

        return $this->mapNames;
    }

    public function getSources(): array
    {
        return array_values(array_diff(array_keys($this->config), [self::COMMON_CONFIG]));
    }

    private function getMapConfigs(): array
    {
        if (null === $this->mapConfig) {
            $result = [];
            foreach ($this->config as $source => $configs) {
                foreach ($configs as $configName => $configData) {
                    if ('map' === $configData['type']) {
                        $values = $configData['values'];
                        $result[$source][$configName] = $values;
                    }
                }
            }
            $this->mapConfig = $result;
        }

        return $this->mapConfig;
    }
}
