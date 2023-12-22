<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Form;

use PHPUnit\Framework\TestCase;
use CrowdSec\Whm\Form\AcquisitionType;
use CrowdSec\Whm\Tests\PHPUnitUtil;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

final class AcquisitionTypeTest extends TestCase
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

    }

    protected function tearDown(): void {
        putenv('CROWDSEC_CONFIG_PATH'); // Reset the env variable
    }


    public function testHandleSingleConfigForStringType(): void
    {
        $acquisitionType = new AcquisitionType();

        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleSingleConfig', ['name' => 'test', 'config' => ['type' =>
                'string']]);

        $this->assertEquals(TextType::class, $result['class']);
    }

    public function testHandleSingleConfigForOtherAttributes(): void
    {
        $acquisitionType = new AcquisitionType();

        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleSingleConfig',
                ['name' => 'test_name', 'config' => [
                    'type' => 'string',
                    'label' => 'test-label',
                    'help' => 'test-help',
                    'required' => true,
                ], 'prefix' => 'test_prefix_'
                ]);

        $this->assertEqualsCanonicalizing(
            [
                'class' => TextType::class,
                'label' => 'test-label',
                'name'=> 'test_prefix_test_name',
                'required' => true,
                'help' => 'test-help',
            ],
            $result
        );
    }

    public function testHandleSingleConfigForArrayType(): void
    {
        $acquisitionType = new AcquisitionType();

        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleSingleConfig', ['name' => 'test', 'config' => ['type' =>
                'array']]);

        $this->assertEquals(TextareaType::class, $result['class']);
    }

    public function testHandleSingleConfigForEnumType(): void
    {
        $acquisitionType = new AcquisitionType();

        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleSingleConfig', ['name' => 'test-name', 'config' => ['type' =>
                'enum', 'values' => ['test1', 'test2']]]);

        $this->assertEquals(ChoiceType::class, $result['class']);
        $this->assertEquals('test-name', $result['name']);
        $this->assertEquals(['test1' => 'test1', 'test2' => 'test2'], $result['choices']);
    }

    public function testHandleSingleConfigForIntegerType(): void
    {
        $acquisitionType = new AcquisitionType();

        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleSingleConfig', ['name' => 'test', 'config' => ['type' =>
                'integer']]);

        $this->assertEquals(IntegerType::class, $result['class']);
    }


    public function testHandleSingleConfigForBooleanType(): void
    {
        $acquisitionType = new AcquisitionType();

        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleSingleConfig', ['name' => 'test', 'config' => ['type' =>
                'boolean']]);

        $this->assertEquals(ChoiceType::class, $result['class']);
        $this->assertEquals(['true' => 'true', 'false' => 'false'], $result['choices']);
    }

    public function testHandleConfigReturnsSingleConfigForNonMapType(): void
    {
        $acquisitionType = new AcquisitionType();

        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleConfig', ['name' => 'test', 'config' => ['type' =>
                'boolean']]);


        $this->assertCount(1, $result);
        $this->assertEquals(ChoiceType::class, $result[0]['class']);
    }

    public function testHandleConfigReturnsMultipleConfigsForMapType(): void
    {
        $acquisitionType = new AcquisitionType();


        $config = ['type' => 'map', 'values' => ['value1' => ['type' => 'string'], 'value2' => ['type' => 'integer']]];
        $result =
            PHPUnitUtil::callMethod($acquisitionType, 'handleConfig', ['name' => 'test', 'config' => $config]);


        $this->assertCount(2, $result);
        $this->assertEquals(TextType::class, $result[0]['class']);
        $this->assertEquals(IntegerType::class, $result[1]['class']);
    }

    public function testBuildForm(): void
    {
        $builderMock = $this->createMock(FormBuilderInterface::class);
        $callCount = 0;
        $expectedCount= 4;
        $builderMock->method('add')
            ->willReturnCallback(function ($fieldName, $fieldType, $options) use (&$callCount, $builderMock) {
                if (in_array($fieldName, ['filepath', 'common_source','file_filenames', 'save'])) {
                    $callCount++;
                }

                return $this->returnValue($builderMock);
            });



        $acquisitionType = new AcquisitionType();
        $acquisitionType->buildForm($builderMock, []);

        $this->assertEquals($expectedCount, $callCount);


    }

}
