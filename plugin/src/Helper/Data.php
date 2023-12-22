<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Helper;

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

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function recursiveKsort(&$item): void
    {
        if (is_array($item)) {
            ksort($item);
        }
    }

    public function hash(array $array): string
    {
        ksort($array);
        array_walk_recursive($array, [$this, 'recursiveKsort']);

        return \hash('sha256', json_encode($array));
    }


}
