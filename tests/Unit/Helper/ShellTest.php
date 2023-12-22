<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Helper;

use CrowdSec\Whm\Helper\Shell;
use CrowdSec\Whm\Exception;
use PHPUnit\Framework\TestCase;
use CrowdSec\Whm\Tests\PHPUnitUtil;

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
        $shell = new Shell();

        $reflector = new \ReflectionObject($shell);

        // Get the private property commandWhitelist
        $property = $reflector->getProperty('commandWhitelist');
        $property->setAccessible(true);

        // Get the current value of commandWhitelist
        $currentWhitelist = $property->getValue($shell);

        // Add a new value to the whitelist array
        $currentWhitelist[] = 'echo -n "ok"';

        // Set the modified array back to the commandWhitelist property
        $property->setValue($shell, $currentWhitelist);

        $result = $shell->exec('echo -n "ok"');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('return_code', $result);
        $this->assertEquals(0, $result['return_code']);
        $this->assertEquals('ok', $result['output']);
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


    public function testgetReadFileAcquisitions(): void
    {
        $shell = $this->getMockBuilder(Shell::class)
            ->setMethods(['exec'])
            ->getMock();


        $metrics = file_get_contents(__DIR__ . '../../../MockedData/metrics.json');

        $shell->method('exec')->willReturn(['return_code' => 0, 'output' => $metrics]);

        $result = PHPUnitUtil::callMethod($shell, 'getReadFileAcquisitions', []);

        $this->assertIsArray($result);
        $expected = array (
            0 => '/var/log/messages',
            1 => '/var/log/secure',
        );
        $this->assertEquals($expected, $result);
    }


    public function testHasNoExecFunc(): void
    {
        $shell = new Shell();

        $result = PHPUnitUtil::callMethod($shell, 'hasNoExecFunc', []);

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

}
