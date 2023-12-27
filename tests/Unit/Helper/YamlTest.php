<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Helper;

use CrowdSec\Whm\Helper\Yaml;
use CrowdSec\Whm\Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use CrowdSec\Whm\Tests\PHPUnitUtil;

final class YamlTest extends TestCase
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
        $acquisitionContentSimple =
            file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.d/test-simple.yaml');

        vfsStream::newFile('test-simple.yaml')
            ->at($acquisCustomDir)
            ->setContent($acquisitionContentSimple);

        $acquisitionContentMulti =
            file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.d/test-multi.yaml');

        vfsStream::newFile('test-multi.yaml')
            ->at($acquisCustomDir)
            ->setContent($acquisitionContentMulti);
    }

    protected function tearDown(): void
    {
        putenv('CROWDSEC_CONFIG_PATH'); // Reset the env variable
    }

    public function testConvertFormToYaml(): void
    {
        $formData = [
            'syslog_max_message_len' => '',
            'syslog_listen_addr' => '',
            'syslog_listen_port' => '',
            'syslog_protocol' => '',
            's3_max_buffer_size' => '',
            's3_sqs_format' => '',
            's3_sqs_name' => '',
            's3_polling_interval' => '',
            's3_polling_method' => '',
            's3_key' => '',
            's3_prefix' => '',
            's3_bucket_name' => '',
            's3_aws_endpoint' => '',
            's3_aws_region' => '',
            's3_aws_profile' => '',
            'k8s_audit_webhook_path' => '',
            'k8s_audit_listen_port' => '',
            'k8s_audit_listen_addr' => '',
            'kinesis_max_retries' => '',
            'kinesis_from_subscription' => '',
            'kinesis_consumer_name' => '',
            'kinesis_aws_endpoint' => '',
            'kinesis_aws_region' => '',
            'kinesis_aws_profile' => '',
            'kinesis_use_enhanced_fanout' => '',
            'kinesis_stream_arn' => '',
            'kinesis_stream_name' => '',
            'kafka_tls_ca_cert' => '',
            'kafka_tls_client_key' => '',
            'kafka_tls_client_cert' => '',
            'kafka_tls_insecure_skip_verify' => '',
            'kafka_timeout' => '',
            'kafka_group_id' => '',
            'kafka_topic' => '',
            'kafka_brokers' => '',
            'docker_force_inotify' => '',
            'docker_container_id_regexp' => '',
            'docker_container_name_regexp' => '',
            'docker_container_id' => '',
            'docker_container_name' => '',
            'docker_docker_host' => '',
            'docker_since' => '',
            'docker_until' => '',
            'docker_follow_stderr' => '',
            'docker_follow_stdout' => '',
            'docker_check_interval' => '',
            'cloudwatch_aws_region' => '',
            'cloudwatch_aws_config_dir' => '',
            'cloudwatch_prepend_cloudwatch_timestamp' => '',
            'cloudwatch_aws_profile' => '',
            'cloudwatch_aws_api_timeout' => '',
            'cloudwatch_stream_read_timeout' => '',
            'cloudwatch_poll_stream_interval' => '',
            'cloudwatch_max_stream_age' => '',
            'cloudwatch_poll_new_stream_interval' => '',
            'cloudwatch_getlogeventspages_limit' => '',
            'cloudwatch_describelogstreams_limit' => '',
            'cloudwatch_stream_name' => '',
            'cloudwatch_stream_regexp' => '',
            'cloudwatch_group_name' => '',
            'journalctl_journalctl_filter' => '',
            'file_poll_without_inotify' => '',
            'file_max_buffer_size' => '',
            'file_force_inotify' => '',
            'file_filename' => '',
            'file_exclude_regexps' => '',
            'common_transform' => '',
            'common_unique_id' => '',
            'common_use_time_machine' => 'true',
            'common_name' => '',
            'common_labels_external_format' => '',
            'common_mode' => '',
            'common_log_level' => 'panic',
            'common_source' => 'file',
            'common_labels_type' => 'syslog',
            'file_filenames' => '/var/log/test.log',
            'filepath' => 'test2.yaml',
        ];

        $yaml = new Yaml();
        $result = $yaml->convertFormToYaml($formData);

        $expexted = [
            'filepath' => 'vfs://etc/crowdsec/acquis.d/test2.yaml',
            'source' => 'file',
            'log_level' => 'panic',
            'use_time_machine' => true,
            'labels' => [
                'type' => 'syslog'
            ],

            'filenames' => [
                '/var/log/test.log'
            ]

        ];

        $this->assertEquals($expexted, $result);
    }

    public function testConvertYamlToForm(): void
    {
        $yamlData = [
            'name' => 'test_name',
            'source' => 'file',
            'log_level' => 'debug',
            'max_buffer_size' => 250,
            'force_inotify' => true,
            'poll_without_inotify' => true,
            'labels' => [
                'type' => 'syslog',
            ],

            'filenames' => [
                '0' => 'eeeeee'
            ],

            'filepath' => 'vfs://etc/crowdsec/acquis.d/test2.yaml',

        ];

        $yaml = new Yaml();
        $result = $yaml->convertYamlToForm($yamlData);

        $expexted = [
            'filepath' => 'test2.yaml',
            'file_filenames' => 'eeeeee',
            'common_name' => 'test_name',
            'common_labels_type' => 'syslog',
            'file_force_inotify' => 'true',
            'docker_force_inotify' => 'true',
            'file_poll_without_inotify' => 'true',
            'file_max_buffer_size' => 250,
            's3_max_buffer_size' => 250,
            'common_source' => 'file',
            'common_log_level' => 'debug',

        ];

        $this->assertEquals($expexted, $result);
    }

    public function testDeleteSimpleYamlAcquisitionByHash()
    {
        $this->assertEquals(
            true,
            file_exists($this->root->url() . '/crowdsec/acquis.d/test-simple.yaml'),
            'Yaml File should exist'
        );

        $yaml = new Yaml();
        $yaml->deleteYamlAcquisitionByHash('58b22815f2c7a7ab252c6750dcb9df71ef0649fe46a6a3dab76eaa66ca51304e');//hash of test-simple.yaml

        $this->assertEquals(
            false,
            file_exists($this->root->url() . '/crowdsec/acquis.d/test-simple.yaml'),
            'Yaml File should not exist anymore'
        );
    }

    public function testDeleteMultiYamlAcquisitionByHash()
    {
        $this->assertEquals(
            true,
            file_exists($this->root->url() . '/crowdsec/acquis.d/test-multi.yaml'),
            'Yaml File should exist'
        );

        $yaml = new Yaml();
        $hash = 'b52f487524e87a6256ca3843e90fdb2151f83ebf4c03bcebc707354a6b1996a1';//hash of test-multi.yaml

        $acquis = $yaml->getYamlAcquisitionByHash($hash);

        $allAcquis = $yaml->getAcquisFromYamls();

        $this->assertEquals(
            7,
            count($allAcquis)
        );

        $expected = array(
            'source' => 'file',
            'log_level' => 'panic',
            'labels' =>
                array(
                    'type' => 'syslog',
                ),
            'filenames' =>
                array(
                    0 => '/var/log/test.log',
                ),
            'filepath' => 'vfs://etc/crowdsec/acquis.d/test-multi.yaml',
        );

        $this->assertEquals(
            $expected,
            $acquis
        );

        $yaml->deleteYamlAcquisitionByHash($hash);

        $this->assertEquals(
            true,
            file_exists($this->root->url() . '/crowdsec/acquis.d/test-multi.yaml'),
            'Yaml File should still exist'
        );

        $allAcquis = $yaml->getAcquisFromYamls();

        $this->assertEquals(
            6,
            count($allAcquis)
        );
    }


    public function testUpsertYamlAcquisitionByHashInMulti(){

        $origContent = file_get_contents($this->root->url() . '/crowdsec/acquis.d/test-multi.yaml');
        $expectedContent = file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.d/test-multi.yaml');

        $this->assertEquals(
            $expectedContent,
            $origContent,
            'Yaml File should be the same'
        );

        $yaml = new Yaml();
        $newAcquis = array(
            'source' => 'file',
            'log_level' => 'debug',
            'labels' =>
                array(
                    'type' => 'syslog',
                ),
            'filenames' =>
                array(
                    0 => '/var/log/test-new.log',
                ),
            'filepath' => 'vfs://etc/crowdsec/acquis.d/test-multi.yaml',
        );
        $newHash = $yaml->hash($newAcquis);

        $yaml->upsertYamlAcquisitionByHash($newHash, 'vfs://etc/crowdsec/acquis.d/test-multi.yaml', $newAcquis);

        $newContent = file_get_contents($this->root->url() . '/crowdsec/acquis.d/test-multi.yaml');
        $expectedNewContent = file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.d/test-multi-with-upserted.yaml');


        $this->assertEquals(
            $expectedNewContent,
            $newContent,
            'Yaml File should not be the same'
        );

    }

    public function testUpsertYamlAcquisitionByHashInNew(){

        $this->assertEquals(
            false,
            file_exists($this->root->url() . '/crowdsec/acquis.d/test-simple-new.yaml'),
            'Yaml File should not exist'
        );

        $yaml = new Yaml();
        $newAcquis = array(
            'source' => 'file',
            'log_level' => 'error',
            'labels' =>
                array(
                    'type' => 'syslog',
                ),
            'filenames' =>
                array(
                    0 => '/var/log/test-simple-new.log',
                ),
            'filepath' => 'vfs://etc/crowdsec/acquis.d/test-simple-new.yaml',
        );
        $newHash = $yaml->hash($newAcquis);

        $yaml->upsertYamlAcquisitionByHash($newHash, 'vfs://etc/crowdsec/acquis.d/test-simple-new.yaml', $newAcquis);

        $this->assertEquals(
            true,
            file_exists($this->root->url() . '/crowdsec/acquis.d/test-simple-new.yaml'),
            'Yaml File should exist'
        );

        $newContent = file_get_contents($this->root->url() . '/crowdsec/acquis.d/test-simple-new.yaml');
        $expectedNewContent = file_get_contents(__DIR__ . '../../../MockedData/etc/crowdsec/acquis.d/test-simple-new.yaml');

        $this->assertEquals(
            $expectedNewContent,
            $newContent,
            'Yaml File should be the same'
        );

    }

}
