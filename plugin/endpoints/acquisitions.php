<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '/usr/local/cpanel/php/WHM.php';

use CrowdSec\Whm\Acquisition\YamlCollection;
use CrowdSec\Whm\Constants;
use CrowdSec\Whm\Helper\Data as Helper;
use CrowdSec\Whm\Helper\Shell;
use CrowdSec\Whm\Template;
use Symfony\Component\HttpFoundation\Session\Session;

$session = new Session();
$session->start();
$helper = new Helper();
$shell = new Shell();

$readFiles = $shell->getReadFileAcquisitions();
$lastRestartSince = $shell->getLastRestartSince();

error_log('lastRestartSignal: ' . $lastRestartSince);

$template = new Template('acquisitions.html.twig');
$yamlAcquis = new YamlCollection();
$yamlAcquisItems = $yamlAcquis->getItems();

WHM::header(Constants::CONTENT_TITLE);
echo $template->render([
    'acquisitions' => $yamlAcquisItems,
    'main_file' => $helper->getAcquisPath(),
    'restart_needed' => $session->get('crowdsec_restart_needed'),
    'no_exec_func' => $shell->hasNoExecFunc(),
    'read_files' => $readFiles,
    'unread_warning_class' => $lastRestartSince > 300 ? 'danger' : 'warning',
]);
WHM::footer();
