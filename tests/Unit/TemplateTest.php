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

final class TemplateTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertEquals(
            'test',
            'test',
            'This is a test test'
        );
    }
}
