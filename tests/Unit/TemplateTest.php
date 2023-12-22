<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit;

/**
 * Test for template.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2022+ CrowdSec
 * @license   MIT License
 */


use PHPUnit\Framework\TestCase;
use CrowdSec\Whm\Template;
use CrowdSec\Whm\Form\AcquisitionType;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

final class TemplateTest extends TestCase
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
        $configContent = file_get_contents(__DIR__ . '../../MockedData/etc/crowdsec/config.yaml');

        vfsStream::newFile('config.yaml')
            ->at($crowdsecDirectory)
            ->setContent($configContent);

    }

    protected function tearDown(): void {
        putenv('CROWDSEC_CONFIG_PATH'); // Reset the env variable
    }

    public function testConstructorCreatesTemplateWithoutFormWhenFormTypeClassIsEmpty()
    {
        $template = new Template('status.html.twig');

        $this->assertNull($template->getForm());
    }

    public function testConstructorCreatesTemplateWithFormWhenFormTypeClassIsNotEmpty()
    {

        $this->assertEquals(
            true,
            file_exists($this->root->url() . '/crowdsec/config.yaml'),
            'Config File should  exist'
        );

        $template = new Template('acquisitions-edit.html.twig', AcquisitionType::class);

        $this->assertNotNull($template->getForm());
    }

    public function testConstructorThrowsExceptionWhenInvalidPathProvided()
    {
        $this->expectException(\Twig\Error\LoaderError::class);

        new Template('invalid/path');
    }


    public function testGetFormReturnsNullWhenNoFormTypeClassProvided()
    {
        $template = new Template('status.html.twig');

        $this->assertNull($template->getForm());
    }

    public function testGetFormReturnsFormWhenFormTypeClassProvided()
    {

        $template = new Template('acquisitions-edit.html.twig', AcquisitionType::class);

        $this->assertNotNull($template->getForm());
    }


}
