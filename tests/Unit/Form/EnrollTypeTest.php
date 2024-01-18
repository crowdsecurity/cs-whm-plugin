<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Form;

use CrowdSec\Whm\Form\EnrollType;
use CrowdSec\Whm\Tests\PHPUnitUtil;
use Symfony\Component\Form\FormBuilderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CrowdSec\Whm\Form\EnrollType::buildForm
 *
 */
final class EnrollTypeTest extends TestCase
{

    public function testBuildForm(): void
    {
        $builderMock = $this->createMock(FormBuilderInterface::class);
        $callCount = 0;
        $expectedCount = 4;
        $builderMock->method('add')
            ->willReturnCallback(function ($fieldName, $fieldType, $options) use (&$callCount, $builderMock) {
                if (in_array($fieldName, ['key', 'name', 'tags', 'overwrite', 'save'])) {
                    ++$callCount;
                }

                return $this->returnValue($builderMock);
            });

        $enrollType = new EnrollType();
        $enrollType->buildForm($builderMock, []);

        $this->assertEquals($expectedCount, $callCount);
    }
}
