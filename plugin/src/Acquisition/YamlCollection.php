<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Acquisition;

use CrowdSec\Whm\Helper\Yaml;

/**
 * The acquisition collection class.
 * List all acquisition based on yaml server files.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
class YamlCollection
{
    /**
     * @var array
     */
    private $items;

    /**
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $yaml = new Yaml();
        $this->items = [];
        $yamlAcquisitions = $yaml->getAcquisFromYamls();

        foreach ($yamlAcquisitions as $yamlAcquisition) {
            $hash = $yaml->hash($yamlAcquisition);

            $this->items[$hash] = $yamlAcquisition;
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
