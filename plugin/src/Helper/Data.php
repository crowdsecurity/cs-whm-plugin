<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Helper;

/**
 * The helper data class.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
class Data
{
    public function error(string $message): void
    {
        error_log('[CrowdSec Plugin log] ' . $message);
    }

    public function getAcquisitionVersion(): string
    {
        return 'v1';
    }

    private function recursiveKsort(&$array): void
    {
        if (is_array($array)) {
            ksort($array);
            foreach ($array as &$item) {
                if (is_array($item)) {
                    $this->recursiveKsort($item);
                }
            }
        }
    }

    public function hash(array $array): string
    {
        $this->recursiveKsort($array);

        return \hash('sha256', json_encode($array));
    }
}
