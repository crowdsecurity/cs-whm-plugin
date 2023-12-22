<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use CrowdSec\Whm\Acquisition\YamlCollection;
use CrowdSec\Whm\Exception;
use CrowdSec\Whm\Tests\UnitPHPUnitUtil;

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
        $configContent = file_get_contents(__DIR__ . '/MockedData/etc/crowdsec/config.yaml');

        vfsStream::newFile('config.yaml')
            ->at($crowdsecDirectory)
            ->setContent($configContent);

        $acquisitionContent = file_get_contents(__DIR__ . '/MockedData/etc/crowdsec/acquis.yaml');

        vfsStream::newFile('acquis.yaml')
            ->at($crowdsecDirectory)
            ->setContent($acquisitionContent);


        $acquisCustomDir = vfsStream::newDirectory('acquis.d')->at($crowdsecDirectory);
        $acquisitionContent = file_get_contents(__DIR__ . '/MockedData/etc/crowdsec/acquis.d/test.yaml');

        vfsStream::newFile('test.yaml')
            ->at($acquisCustomDir)
            ->setContent($acquisitionContent);


        error_log($acquisCustomDir->url());


    }

    protected function tearDown(): void {
        putenv('CROWDSEC_CONFIG_PATH'); // Reset the env variable
    }

    public function testConstructorCreatesYamlCollectionWithYamlAcquisition()
    {

        $yamlCollection = new YamlCollection();

        $items = $yamlCollection->getItems();

        $this->assertCount(6, $items);


        $expected = array (
            '49112c931a62bfbf1333380a1570fa756c6ad645208518bbf4415d22d6a45358' =>
                array (
                    'filenames' =>
                        array (
                            0 => '/var/log/apache2/modsec_audit.log',
                            1 => '/var/log/apache2/modsec_debug.log',
                        ),
                    'labels' =>
                        array (
                            'type' => 'apache2',
                        ),
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ),
            '92b68b9f29474dbc3f7e6292860062f4fa6e4a40d509727cf34642d90c2649a0' =>
                array (
                    'filenames' =>
                        array (
                            0 => '/var/log/secure',
                        ),
                    'labels' =>
                        array (
                            'type' => 'syslog',
                        ),
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ),
            'f3a400515bc8aba2353230b14953d3afb9d8d52c0f001caa6b1f5121fc15565c' =>
                array (
                    'journalctl_filter' =>
                        array (
                            0 => '_SYSTEMD_UNIT=mysql.service',
                        ),
                    'labels' =>
                        array (
                            'type' => 'mysql',
                        ),
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ),
            'c8f542ab27941961db1e1eba6ed1eab732f8ec467f7113582760509bd3704b86' =>
                array (
                    'filenames' =>
                        array (
                            0 => '/var/log/anaconda/syslog',
                            1 => '/var/log/messages',
                        ),
                    'labels' =>
                        array (
                            'type' => 'syslog',
                        ),
                    'filepath' => 'vfs://etc/crowdsec/acquis.yaml',
                ),
            '6b1a033678b52b968ce2eb2a4d9261e4a03ba410c093724ae50bdc03925125a4' =>
                array (
                    'source' => 'file',
                    'log_level' => 'panic',
                    'labels' =>
                        array (
                            'type' => 'syslog',
                        ),
                    'filenames' =>
                        array (
                            0 => '/var/log/test.log',
                        ),
                    'filepath' => 'vfs://etc/crowdsec/acquis.d/test.yaml',
                ),
            '177b9f825a7a1b0ea751bbb4387aa9e0aa18e86d6664f22957b7b7a2f27ec06c' =>
                array (
                    'filenames' =>
                        array (
                            0 => 'eeeeee',
                        ),
                    'log_level' => 'trace',
                    'labels' =>
                        array (
                            'type' => 'syslog',
                        ),
                    'source' => 'file',
                    'filepath' => 'vfs://etc/crowdsec/acquis.d/test.yaml',
                ),
        );

        $this->assertEqualsCanonicalizing($expected, $items);

    }



}
