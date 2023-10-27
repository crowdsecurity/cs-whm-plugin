<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '/usr/local/cpanel/php/WHM.php';

use CrowdSec\Whm\Constants;
use CrowdSec\Whm\Helper\Shell;
use CrowdSec\Whm\Template;
use Symfony\Component\HttpFoundation\Session\Session;

$shell = new Shell();
$session = new Session();
$session->start();
$template = new Template('status.html.twig');
WHM::header(Constants::CONTENT_TITLE);
echo $template->render(
    [
        'restart_needed' => $session->get('crowdsec_restart_needed'),
        'no_exec_func' => $shell->hasNoExecFunc(),
    ]
);
WHM::footer();
