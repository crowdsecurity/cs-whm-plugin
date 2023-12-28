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

use CrowdSec\Whm\Form\AcquisitionType;
use CrowdSec\Whm\Template;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CrowdSec\Whm\Template::__construct
 *
 * @uses \CrowdSec\Whm\Acquisition\Config::__construct
 * @uses \CrowdSec\Whm\Acquisition\Config::getConfig
 * @uses \CrowdSec\Whm\Form\AcquisitionType::buildForm
 * @uses \CrowdSec\Whm\Form\AcquisitionType::handleConfig
 * @uses \CrowdSec\Whm\Form\AcquisitionType::handleSingleConfig
 * @uses \CrowdSec\Whm\Helper\Data::getAcquisitionVersion
 * @uses \CrowdSec\Whm\Helper\Yaml::getAcquisDir
 * @uses \CrowdSec\Whm\Helper\Yaml::getConfig
 * @uses \CrowdSec\Whm\Helper\Yaml::getYamlContent
 *
 * @covers \CrowdSec\Whm\Template::getForm
 * @covers \CrowdSec\Whm\Template::render
 */
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

    protected function tearDown(): void
    {
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

    public function testRenderWithNonEmptyConfig(): void
    {
        $config = ['test_key' => 'test_value'];

        putenv('cp_security_token=test_token');
        $template = new Template('test.html.twig', '', [], __DIR__ . '/../templates');

        $expected = <<<EOT
<p>test_token</p>
<p>test_value</p>

EOT;

        $this->assertEquals($expected, $template->render($config));

        putenv('cp_security_token');
    }

    public function testTemplateForAForm(): void
    {
        $config = ['test_key' => 'test_value'];

        putenv('cp_security_token=test_token');
        $formData = [];
        $template = new Template('test.html.twig', AcquisitionType::class, $formData, __DIR__ . '/../templates');
        $expected = <<<EOT
<p>test_token</p>
<p>test_value</p>

EOT;
        $this->assertEquals($expected, $template->render($config));

        putenv('cp_security_token');

        $form = $template->getForm();

        $this->assertInstanceOf(\Symfony\Component\Form\Form::class, $form);
    }
}
