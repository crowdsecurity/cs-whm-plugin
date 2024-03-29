<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Helper;

use CrowdSec\Whm\Exception;
use CrowdSec\Whm\Helper\Shell;
use CrowdSec\Whm\Tests\PHPUnitUtil;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CrowdSec\Whm\Helper\Shell::checkConfig
 * @covers \CrowdSec\Whm\Helper\Shell::escapeShellCmd
 * @covers \CrowdSec\Whm\Helper\Shell::exec
 * @covers \CrowdSec\Whm\Helper\Shell::getExecFunc
 * @covers \CrowdSec\Whm\Helper\Shell::getLastRestart
 * @covers \CrowdSec\Whm\Helper\Shell::getLastRestartSince
 * @covers \CrowdSec\Whm\Helper\Shell::getAcquisitionMetrics
 * @covers \CrowdSec\Whm\Helper\Shell::getMetrics
 * @covers \CrowdSec\Whm\Helper\Shell::getReadAcquisitionsBySource
 * @covers \CrowdSec\Whm\Helper\Shell::getReadFileAcquisitions
 * @covers \CrowdSec\Whm\Helper\Shell::hasNoExecFunc
 * @covers \CrowdSec\Whm\Helper\Shell::getWhitelist
 * @covers \CrowdSec\Whm\Helper\Shell::enroll
 * @covers \CrowdSec\Whm\Helper\Shell::getConfigs
 */
final class ShellTest extends TestCase
{
    public function testCheckConfigThrowsExceptionWhenExecReturnsNonZero(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();
        $shell->method('exec')->willReturn(['return_code' => 1, 'output' => 'Invalid config']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid config: Invalid config');

        $shell->checkConfig();
    }

    public function testCheckConfigReturnsTrueWhenExecReturnsZero(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();
        $shell->method('exec')->willReturn(['return_code' => 0, 'output' => '']);

        $this->assertTrue($shell->checkConfig());
    }

    public function testEnroll(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $shell->expects($this->once())
            ->method('exec')
            ->with(
                $this->equalTo('cscli console enroll'),
                $this->equalTo(' --name \'my-name\' --tags \'my-tag\' --overwrite \'my-key\' 2>&1')
            )
            ->willReturn(['return_code' => 0, 'output' => '']);

        $shell->enroll('my-key', 'my-name', ['my-tag'], true);
    }

    public function testEnroll2(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $shell->expects($this->once())
            ->method('exec')
            ->with(
                $this->equalTo('cscli console enroll'),
                $this->equalTo(' --name \'my-name\' --tags \'my-tag1\' --tags \'my-tag2\' \'my-key\' 2>&1')
            )
            ->willReturn(['return_code' => 0, 'output' => '']);

        $shell->enroll('my-key', 'my-name', ['my-tag1', 'my-tag2']);
    }

    public function testEnrollException()
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $shell
            ->method('exec')
            ->willReturn(['return_code' => 1, 'output' => 'Not good']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong: Not good');

        $shell->enroll('my-key', 'my-name', ['my-tag1', 'my-tag2']);
    }

    public function testEnrollOverwriteException()
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $shell
            ->method('exec')
            ->willReturn(['return_code' => 0, 'output' => 'Some message with \'overwrite\' inside']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Instance is already enrolled. You can use the overwrite option to force enroll.');

        $shell->enroll('my-key', 'my-name', ['my-tag1', 'my-tag2']);
    }

    public function testExecReturnsNoExecFuncWhenNoExecFuncAvailable(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['getExecFunc'])
            ->getMock();

        $shell->method('getExecFunc')->willReturn(Shell::NO_EXEC_FUNC);

        $result = $shell->exec('any command');

        $this->assertEquals(['output' => Shell::NO_EXEC_FUNC, 'return_code' => -1], $result);
    }

    public function testExecReturnsCommandNotAllowedWhenCommandNotInWhitelist(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['getExecFunc'])
            ->getMock();

        $shell->method('getExecFunc')->willReturn('exec');

        $result = $shell->exec('not in whitelist command');

        $this->assertEquals(['output' => 'Command not allowed', 'return_code' => -1], $result);
    }

    public function testExecReturnsOutputAndReturnCodeWhenCommandInWhitelist(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['getWhitelist'])
            ->getMock();

        $shell->method('getWhitelist')->willReturn(['echo -n "ok"']);

        $result = $shell->exec('echo -n "ok"');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('return_code', $result);
        $this->assertEquals(0, $result['return_code']);
        $this->assertEquals('ok', $result['output']);
    }

    public function testExecStderrToStdout()
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['getWhitelist'])
            ->getMock();

        $shell->method('getWhitelist')->willReturn(['echo -n "ok" 2>&1']);

        $result = $shell->exec('echo -n "ok" 2>&1');

        $expected = [
            'return_code' => 0,
            'output' => 'ok',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetLastRestartSince(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $currentTime = time();
        $lastRestartSince = 10;
        $dateString = date('D Y-m-d H:i:s e', $currentTime - $lastRestartSince);
        $shell->method('exec')->willReturn(['return_code' => 0, 'output' => $dateString]);

        $result = PHPUnitUtil::callMethod($shell, 'getLastRestartSince', []);

        $this->assertEquals($lastRestartSince, $result);
    }

    public function testGetConfigs(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();


        $shell->method('exec')->willReturnOnConsecutiveCalls(
            ['output' => '127.0.0.1:8888', 'return_code' => 0],
            ['output' => '1234', 'return_code' => 0]
        );

        $result = PHPUnitUtil::callMethod($shell, 'getConfigs', []);

        $expected = [
            'lapi_port' => '8888',
            'lapi_host' => '127.0.0.1',
            'prometheus_port' => '1234',
        ];


        $this->assertEquals($expected, $result);
    }

    public function testGetLastRestartSinceWhenFailed(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $currentTime = time();
        $shell->method('exec')->willReturn(['return_code' => 1, 'output' => 'Something when wrong']);

        $result = $shell->getLastRestartSince();

        $this->assertEquals($currentTime, $result);
    }

    public function testgetReadFileAcquisitions(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $metrics = file_get_contents(__DIR__ . '../../../MockedData/metrics.json');

        $shell->method('exec')->willReturn(['return_code' => 0, 'output' => $metrics]);

        $result = PHPUnitUtil::callMethod($shell, 'getReadFileAcquisitions', []);

        $this->assertIsArray($result);
        $expected = [
            0 => '/var/log/messages',
            1 => '/var/log/secure',
        ];
        $this->assertEquals($expected, $result);
    }

    public function testHasNoExecFunc(): void
    {
        $shell = new Shell();

        $result = PHPUnitUtil::callMethod($shell, 'hasNoExecFunc', []);

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testExecCall()
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['getExecFunc', 'getWhitelist'])
            ->getMock();

        $shell->method('getExecFunc')->willReturn('exec');
        $shell->method('getWhitelist')->willReturn(['echo -n "ok"']);

        $result = $shell->exec('echo -n "ok"');

        $expected = [
            'return_code' => 0,
            'output' => 'ok',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetWhitelist()
    {
        $shell = new Shell();

        $result = PHPUnitUtil::callMethod($shell, 'getWhitelist', []);

        $expected = [
            'cscli alerts list -l 0 -o json',
            'cscli bouncers list -o json',
            'cscli collections list -o json',
            'cscli decisions list -l 0 -o json',
            'cscli machines list -o json',
            'cscli parsers list -o json',
            'cscli postoverflows list -o json',
            'cscli scenarios list -o json',
            'cscli metrics -o json',
            'systemctl is-active crowdsec',
            'systemctl restart crowdsec',
            'crowdsec -t 2>&1',
            'systemctl show -p ActiveEnterTimestamp --value crowdsec',
            'cscli console enroll',
            'cscli config show --key Config.API.Server.ListenURI',
            'cscli config show --key Config.Prometheus.ListenPort',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetMetricsEmpty(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();

        $shell->method('exec')->willReturn(['return_code' => 1, 'output' => 'Something when wrong']);

        $result = PHPUnitUtil::callMethod($shell, 'getMetrics', []);

        $this->assertEquals([], $result);
    }
}
