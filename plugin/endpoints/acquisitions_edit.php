<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';
require_once '/usr/local/cpanel/php/WHM.php';

use CrowdSec\Whm\Acquisition\YamlCollection;
use CrowdSec\Whm\Constants;
use CrowdSec\Whm\Exception;
use CrowdSec\Whm\Form\AcquisitionType;
use CrowdSec\Whm\Helper\Data as Helper;
use CrowdSec\Whm\Helper\Shell;
use CrowdSec\Whm\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

$request = Request::createFromGlobals();

$session = new Session();
$session->start();
$flashes = $session->getFlashBag();
$helper = new Helper();
$shell = new Shell();
$currentHash = (string) $request->query->get('id');

$formData = [];
$yamlAcquisition = false;
if ($currentHash) {
    $yamlAcquisition = $helper->getYamlAcquisitionByHash($currentHash);

    $formData = $helper->convertYamlToForm($yamlAcquisition);
}
$template = new Template('acquisitions-edit.html.twig', AcquisitionType::class, $formData);

$yamlAcquis = new YamlCollection();
$yamlAcquisItems = $yamlAcquis->getItems();
$form = $template->getForm();
$form->handleRequest();

if ($form->isSubmitted()) {
    $responseParam = '';
    $success = false;
    $formData = array_reverse($form->getData(), true);

    $newData = $helper->convertFormToYaml($formData);

    $newHash = $helper->hash($newData);
    $responseParam = '?id=' . $newHash;

    $hashToCompare = $currentHash ?: $newHash;

    $newDataFilepath = $newData['filepath'];
    $savedResult = $helper->upsertYamlAcquisitionByHash($hashToCompare, $newDataFilepath, $newData);

    if ($savedResult) {
        $success = true;
        try {
            $shell->checkConfig();
        } catch (Exception $e) {
            if (!$currentHash) {
                $helper->deleteYamlAcquisitionByHash($newHash, true);
            } else {
                $oldFilepath = $yamlAcquisition['filepath'];
                $helper->upsertYamlAcquisitionByHash($newHash, $oldFilepath, $yamlAcquisition);

                $responseParam = '?id=' . $currentHash;
            }

            $success = false;
            $flashes->add('error', 'Something went wrong while saving acquisition.');
            $flashes->add('error', $e->getMessage());
        }
    }

    if ($success) {
        $flashes->add('success', 'Acquisition saved successfully.');
        $flashes->add('notice', 'Please restart the CrowdSec service to apply changes.');
        $session->set('crowdsec_restart_needed', true);
    }

    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $response = new RedirectResponse($uri . $responseParam);

        return $response->send();
    }
}

WHM::header(Constants::CONTENT_TITLE);

echo $template->render([
    'acquisitions' => $yamlAcquisItems,
    'form' => $form->createView(),
    'current' => $yamlAcquisition,
    'flashes' => $flashes->all(),
    'restart_needed' => $session->get('crowdsec_restart_needed'),
    'no_exec_func' => $shell->hasNoExecFunc(),
]);
WHM::footer();
