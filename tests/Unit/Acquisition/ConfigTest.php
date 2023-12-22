<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Acquisition;

use PHPUnit\Framework\TestCase;
use CrowdSec\Whm\Acquisition\Config;
use CrowdSec\Whm\Exception;
use CrowdSec\Whm\Tests\PHPUnitUtil;

final class ConfigTest extends TestCase
{
    /** @var Config */
    private $config;



    public function constructorThrowsExceptionWhenVersionNotImplemented()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Version not_implemented_version is not implemented');

        new Config('not_implemented_version');
    }

    public function constructorSetsConfigWhenVersionImplemented()
    {
        $config = new Config('1.0');

        $this->assertIsArray($config->getConfig());
        $this->assertNotEmpty($config->getConfig());
    }

    public function testGetConfigReturnsWholeConfigWhenNoSourceProvided()
    {

        $this->config = new Config('v1');
        $result = $this->config->getConfig();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('file', $result);
        $this->assertEqualsCanonicalizing([
            'common',
            'file',
            'journalctl',
            'cloudwatch',
            'kafka',
            'kinesis',
            'k8s_audit',
            's3',
            'syslog',
            'docker',
        ],array_keys($result));
    }

    public function testGetConfigReturnsEmptyArrayWhenInvalidSourceProvided()
    {
        $this->config = new Config('v1');
        $result = $this->config->getConfig('invalid_source');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetConfigReturnsSpecificConfigWhenValidSourceProvided()
    {
        $this->config = new Config('v1');
        $result = $this->config->getConfig('file');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEqualsCanonicalizing([
            'exclude_regexps',
            'filename',
            'filenames',
            'force_inotify',
            'max_buffer_size',
            'poll_without_inotify',
        ],array_keys($result));
    }




    public function testConfigsByTypeReturnsEmptyArrayWhenTypeNotPresent()
    {
        $this->config = new Config('v1');
        $result = $this->config->getConfigsByType('non_existent_type');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testConfigsByTypeReturnsConfigNamesWhenTypePresent()
    {
        $this->config = new Config('v1');
        $result = $this->config->getConfigsByType('map');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEqualsCanonicalizing([
            'common',
            'kafka',
        ],array_keys($result));
    }


    public function testMapNamesReturnsMapConfigNamesWhenMapConfigsExist()
    {
        $this->config = new Config('v1');
        $result = $this->config->getMapNames();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEqualsCanonicalizing([
            'labels',
            'tls',
        ],$result);
    }

    public function testGetMapConfigs()
    {
        $this->config = new Config('v1');
        $mapConfigs = PHPUnitUtil::callMethod($this->config, 'getMapConfigs', []);

        $this->assertIsArray($mapConfigs);
        $this->assertNotEmpty($mapConfigs);
    }


}
