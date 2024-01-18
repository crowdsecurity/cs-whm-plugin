<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '/usr/local/cpanel/php/WHM.php';

use CrowdSec\Whm\Constants;
use CrowdSec\Whm\Form\EnrollType;
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
$yaml = new Yaml();
$shell = new Shell();
$currentHash = (string) $request->query->get('id');

$formData = [];
$yamlAcquisition = false;
if ($currentHash) {
    $yamlAcquisition = $yaml->getYamlAcquisitionByHash($currentHash);
    $formData = $yaml->convertYamlToForm($yamlAcquisition);
}
$template = new Template('enroll.html.twig', EnrollType::class, $formData);

$form = $template->getForm();
$form->handleRequest();

if ($form->isSubmitted()) {
    $formData = $form->getData();

    $key = $formData['key'] ?? '';
    $name = $formData['name'] ?? '';
    $tags = !empty(trim($formData['tags'] ?? '')) ?
        array_filter(array_unique(array_map('trim', explode(\PHP_EOL, $formData['tags']))), function ($value) {
            return '' !== trim($value);
        })
        :
        [];

    $overwrite = $formData['overwrite'] ?? false;

    $success = true;
    try {
        $shell->enroll($key, $name, $tags, $overwrite);
    } catch (\Exception $e) {
        $success = false;
        $flashes->add('error', 'Something went wrong while enrolling security engine.');
        $flashes->add('error', $e->getMessage());
    }

    if ($success) {
        $flashes->add('success', 'Security engine enrolled successfully.');
        $flashes->add('notice', 'Please accept this enrollment in your <a href="https://app.crowdsec.net/">CrowdSec console</a>.');
    }

    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $response = new RedirectResponse($uri);

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
