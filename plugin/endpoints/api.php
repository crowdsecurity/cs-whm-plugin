<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use CrowdSec\Whm\Helper\Shell;
use Symfony\Component\HttpFoundation\Session\Session;

$session = new Session();
$session->start();

$shell = new Shell();
$default = '[]';
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ('POST' === $method && isset($_POST['action'])) {
    $action = strip_tags($_POST['action']);

    switch ($action) {
        case 'status-alerts-list':
            echo $shell->exec('cscli alerts list -l 0 -o json')['output'];

            break;
        case 'status-bouncers-list':
            echo $shell->exec('cscli bouncers list -o json')['output'];

            break;
        case 'status-collections-list':
            echo $shell->exec('cscli collections list -o json')['output'];

            break;
        case 'status-decisions-list':
            echo $shell->exec('cscli decisions list -l 0 -o json')['output'];

            break;
        case 'status-machines-list':
            echo $shell->exec('cscli machines list -o json')['output'];

            break;
        case 'status-parsers-list':
            echo $shell->exec('cscli parsers list -o json')['output'];

            break;
        case 'status-postoverflows-list':
            echo $shell->exec('cscli postoverflows list -o json')['output'];

            break;
        case 'status-scenarios-list':
            echo $shell->exec('cscli scenarios list -o json')['output'];

            break;
        case 'services-status':
            $status = trim($shell->exec('systemctl is-active crowdsec')['output']);
            $result = 'active' === $status ? 'running' : 'not running';
            echo json_encode(
                [
                    'crowdsec-status' => $result,
                ]
            );

            break;
        case 'metrics-acquisition-list':
        case 'metrics-bucket-list':
        case 'metrics-parser-list':
        case 'metrics-alerts-list':
        case 'metrics-lapi-machines-list':
        case 'metrics-lapi-list':
        case 'metrics-lapi-bouncers-list':
        case 'metrics-decisions-list':
            $result = $shell->exec('cscli metrics -o json');
            if (0 !== $result['return_code']) {
                $result = '{"error": "Something went wrong while fetching metrics."}';
            } else {
                $result = $result['output'];
            }
            echo $result;

            break;

        case 'delete-acquisition':
            $acquisId = $_POST['hash'];
            $result = ['error' => 'Something went wrong while deleting acquisition.'];
            if (!$acquisId) {
                $result['error'] = 'Acquisition Id is required';
            }

            if ($shell->deleteYamlAcquisitionByHash($acquisId)) {
                $result['success'] = true;
                unset($result['error']);
                $session->set('crowdsec_restart_needed', true);
            }

            echo json_encode($result);
            break;

        case 'crowdsec-restart':
            $restart = $shell->exec('systemctl restart crowdsec')['return_code'];
            $result = ['error' => 'Something went wrong while restarting service'];
            if (0 === $restart) {
                $result['success'] = true;
                unset($result['error']);
                $session->set('crowdsec_restart_needed', false);
            }
            echo json_encode($result);

            break;
        default:
            echo $default;
    }
} else {
    echo $default;
}
