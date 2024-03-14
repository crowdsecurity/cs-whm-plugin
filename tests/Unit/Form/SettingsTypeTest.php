<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Form;

use CrowdSec\Whm\Form\SettingsType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @covers \CrowdSec\Whm\Form\SettingsType::buildForm
 */
final class SettingsTypeTest extends TestCase
{
    public function testBuildForm(): void
    {
        $builderMock = $this->createMock(FormBuilderInterface::class);
        $callCount = 0;
        $expectedCount = 2;
        $builderMock->method('add')
            ->willReturnCallback(function ($fieldName, $fieldType, $options) use (&$callCount, $builderMock) {
                if (in_array($fieldName, ['lapi_port', 'prometheus_port', 'save'])) {
                    ++$callCount;
                }

                return $this->returnValue($builderMock);
            });

        $enrollType = new SettingsType();
        $enrollType->buildForm($builderMock, []);

        $this->assertEquals($expectedCount, $callCount);
    }
}
