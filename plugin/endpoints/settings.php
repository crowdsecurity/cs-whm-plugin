<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '/usr/local/cpanel/php/WHM.php';

use CrowdSec\Whm\Constants;
use CrowdSec\Whm\Form\SettingsType;
use CrowdSec\Whm\Helper\Shell;
use CrowdSec\Whm\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

$request = Request::createFromGlobals();

$session = new Session();
$session->start();
$flashes = $session->getFlashBag();
$shell = new Shell();

$formData = [];
$template = new Template('settings.html.twig', SettingsType::class, $formData);

$form = $template->getForm();
$form->handleRequest();

if ($form->isSubmitted()) {
    $formData = $form->getData();

   //@TODO: modify config.yaml or config.local.yaml
}

WHM::header(Constants::CONTENT_TITLE);

echo $template->render([
    'form' => $form->createView(),
    'flashes' => $flashes->all(),
    'restart_needed' => $session->get('crowdsec_restart_needed'),
    'no_exec_func' => $shell->hasNoExecFunc(),
]);
WHM::footer();
