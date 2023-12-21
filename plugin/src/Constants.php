<?php

declare(strict_types=1);

namespace CrowdSec\Whm;

/**
 * Every constant of the library are set here.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
class Constants
{
    public const ACQUIS_DIR_DEFAULT = '/etc/crowdsec/acquis.d';
    public const CONFIG_PATH_DEFAULT = '/etc/crowdsec/config.yaml';
    public const CONTENT_TITLE = '<span class="crowdsec-title">CrowdSec for WHM</span>';
    public const TEMPLATES_DIR = __DIR__ . '/templates';
}
