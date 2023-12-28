<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Tests\Unit\Helper;

use CrowdSec\Whm\Helper\Data;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CrowdSec\Whm\Helper\Data::hash
 * @covers \CrowdSec\Whm\Helper\Data::recursiveKsort
 * @covers \CrowdSec\Whm\Helper\Data::getAcquisitionVersion
 */
final class DataTest extends TestCase
{
    public function testHashSortsArrayBeforeHashing(): void
    {
        $data = new Data();
        $array = ['b' => '2', 'a' => '1'];
        $expectedHash = \hash('sha256', json_encode(['a' => '1', 'b' => '2']));

        $this->assertEquals($expectedHash, $data->hash($array));

        $array = ['a' => '1', 'b' => ['d' => '2', 'c' => '1']];
        $expectedHash = \hash('sha256', json_encode(['a' => '1', 'b' => ['c' => '1', 'd' => '2']]));
        $this->assertEquals($expectedHash, $data->hash($array));
    }

    public function testHashReturnsDifferentResultsForDifferentInput(): void
    {
        $data = new Data();
        $array1 = ['b' => '2', 'a' => '1'];
        $array2 = ['b' => '3', 'a' => '1'];

        $this->assertNotEquals($data->hash($array1), $data->hash($array2));
    }

    public function testHashReturnsSameResultsForSortDifference(): void
    {
        $data = new Data();
        $array1 = ['b' => '2', 'a' => '1'];
        $array2 = ['a' => '1', 'b' => '2'];

        $this->assertEquals($data->hash($array1), $data->hash($array2));
    }

    public function testGetAcquisitionVersion(): void
    {
        $data = new Data();
        $this->assertEquals('v1', $data->getAcquisitionVersion());
    }
}
