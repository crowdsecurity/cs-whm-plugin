<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Acquisition;

use CrowdSec\Whm\Acquisition\YamlCollection;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CrowdSec\Whm\Acquisition\YamlCollection::__construct
 * @covers \CrowdSec\Whm\Acquisition\YamlCollection::getItems
 *
 * @uses \CrowdSec\Whm\Helper\Data::hash
 * @uses \CrowdSec\Whm\Helper\Data::recursiveKsort
 * @uses \CrowdSec\Whm\Helper\Yaml::getAcquisDir
 * @uses \CrowdSec\Whm\Helper\Yaml::getAcquisFromYamls
 * @uses \CrowdSec\Whm\Helper\Yaml::getAcquisPath
 * @uses \CrowdSec\Whm\Helper\Yaml::getConfig
 * @uses \CrowdSec\Whm\Helper\Yaml::getMultiYamlContent
 * @uses \CrowdSec\Whm\Helper\Yaml::getOverrideAcquisFiles
 * @uses \CrowdSec\Whm\Helper\Yaml::getYamlContent
 * @uses \CrowdSec\Whm\Helper\Yaml::getYamlContentFromString
 */
final class YamlCollectionTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        putenv('CROWDSEC_CONFIG_PATH=vfs://etc/crowdsec/config.yaml');
        $this->root = vfsStream::setup('/etc');
        $crowdsecDirectory = vfsStream::newDirectory('crowdsec')->at($this->root);
        $configContent = file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/config.yaml');

        vfsStream::newFile('config.yaml')
            ->at($crowdsecDirectory)
            ->setContent($configContent);

        $acquisitionContent = file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.yaml');

        vfsStream::newFile('acquis.yaml')
            ->at($crowdsecDirectory)
            ->setContent($acquisitionContent);

        $acquisCustomDir = vfsStream::newDirectory('acquis.d')->at($crowdsecDirectory);
        $acquisitionContentSimple = file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.d/test-simple.yaml');

        vfsStream::newFile('test-simple.yaml')
            ->at($acquisCustomDir)
            ->setContent($acquisitionContentSimple);

        $acquisitionContentMulti = file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.d/test-multi.yaml');

        vfsStream::newFile('test-multi.yaml')
            ->at($acquisCustomDir)
            ->setContent($acquisitionContentMulti);
    }

    protected function tearDown(): void
    {
        putenv('CROWDSEC_CONFIG_PATH'); // Reset the env variable
    }

    public function testConstructorCreatesYamlCollectionWithYamlAcquisition()
    {
        $yamlCollection = new YamlCollection();

        $items = $yamlCollection->getItems();

        $this->assertCount(7, $items);

        $expected = [
            '49112c931a62bfbf1333380a1570fa756c6ad645208518bbf4415d22d6a45358' => [
                    'filenames' => [
                            0 => '/var/log/apache2/modsec_audit.log',
                            1 => '/var/log/apache2/modsec_debug.log',
                        ],
                    'labels' => [
                            'type' => 'apache2',
                        ],
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ],
            '92b68b9f29474dbc3f7e6292860062f4fa6e4a40d509727cf34642d90c2649a0' => [
                    'filenames' => [
                            0 => '/var/log/secure',
                        ],
                    'labels' => [
                            'type' => 'syslog',
                        ],
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ],
            'f3a400515bc8aba2353230b14953d3afb9d8d52c0f001caa6b1f5121fc15565c' => [
                    'journalctl_filter' => [
                            0 => '_SYSTEMD_UNIT=mysql.service',
                        ],
                    'labels' => [
                            'type' => 'mysql',
                        ],
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ],
            'c8f542ab27941961db1e1eba6ed1eab732f8ec467f7113582760509bd3704b86' => [
                    'filenames' => [
                            0 => '/var/log/anaconda/syslog',
                            1 => '/var/log/messages',
                        ],
                    'labels' => [
                            'type' => 'syslog',
                        ],
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ],
            '58b22815f2c7a7ab252c6750dcb9df71ef0649fe46a6a3dab76eaa66ca51304e' => [
                    'source' => 'file',
                    'log_level' => 'debug',
                    'labels' => [
                            'type' => 'syslog',
                        ],
                    'filenames' => [
                            0 => '/var/log/test-simple.log',
                        ],
                    'filepath' => 'vfs://etc/crowdsec/acquis.d/test-simple.yaml',
                ],
            'b52f487524e87a6256ca3843e90fdb2151f83ebf4c03bcebc707354a6b1996a1' => [
                    'source' => 'file',
                    'log_level' => 'panic',
                    'labels' => [
                            'type' => 'syslog',
                        ],
                    'filenames' => [
                            0 => '/var/log/test.log',
                        ],
                    'filepath' => 'vfs://etc/crowdsec/acquis.d/test-multi.yaml',
                ],
            'd74f4e07eba223bc5400d277d1c8ae96144a38eaaeceeb3219475aad8af13d45' => [
                    'filenames' => [
                            0 => 'eeeeee',
                        ],
                    'log_level' => 'trace',
                    'labels' => [
                            'type' => 'syslog',
                        ],
                    'source' => 'file',
                    'filepath' => 'vfs://etc/crowdsec/acquis.d/test-multi.yaml',
                ],
        ];

        $this->assertEqualsCanonicalizing($expected, $items);
    }
}
