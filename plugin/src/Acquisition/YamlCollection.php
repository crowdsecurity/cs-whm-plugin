<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Acquisition;

use CrowdSec\Whm\Helper\Data as Helper;

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
     * @var Helper
     */
    private $helper;
    /**
     * @var array
     */
    private $items;

    public function __construct()
    {
        $this->helper = new Helper();
        $this->items = [];
        $yamlAcquisitions = $this->helper->getAcquisFromYamls();

        foreach ($yamlAcquisitions as $yamlAcquisition) {
            $hash = $this->helper->hash($yamlAcquisition);

            $this->items[$hash] = $yamlAcquisition;
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
