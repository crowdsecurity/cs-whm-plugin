<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '/usr/local/cpanel/php/WHM.php';

use CrowdSec\Whm\Constants;
use CrowdSec\Whm\Form\SettingsType;
use CrowdSec\Whm\Helper\Shell;
use CrowdSec\Whm\Helper\Yaml;
use CrowdSec\Whm\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

$request = Request::createFromGlobals();

$session = new Session();
$session->start();
$flashes = $session->getFlashBag();
$shell = new Shell();
$yaml = new Yaml();

$currentConfigs = $shell->getConfigs();
$template = new Template('settings.html.twig', SettingsType::class, $currentConfigs);

$form = $template->getForm();
$form->handleRequest();

if ($form->isSubmitted()) {
    $formData = array_merge($form->getData(), ['lapi_host' => $currentConfigs['lapi_host']]);

    if ($formData !== $currentConfigs) {
        $yaml->setLocalConfigs($formData);
        $flashes->add('success', 'Settings updated');
        $flashes->add('notice', 'Please restart the CrowdSec service to apply changes.');
        $session->set('crowdsec_restart_needed', true);
    }

    if (isset($_SERVER['REQUEST_URI'])) {
        $response = new RedirectResponse($_SERVER['REQUEST_URI']);

        return $response->send();
    }
}

WHM::header(Constants::CONTENT_TITLE);

echo $template->render([
    'form' => $form->createView(),
    'flashes' => $flashes->all(),
    'restart_needed' => $session->get('crowdsec_restart_needed'),
    'no_exec_func' => $shell->hasNoExecFunc(),
]);
WHM::footer();
