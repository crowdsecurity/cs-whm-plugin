<?php

declare(strict_types=1);

$default = '[]';
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method === 'POST' && isset($_POST['action'])) {
    $action = strip_tags($_POST['action']);

    switch ($action) {
        case 'services-status':
            ob_start();
            trim(system('systemctl --quiet is-active crowdsec && echo -n \'running\' || echo -n \'not running\''));
            $result = ob_get_contents();
            ob_end_clean();
            //$crowdsec_status = trim(system('systemctl --quiet is-active crowdsec && echo \'running\' || echo \'not running\''));
            echo json_encode(
                [
                    'crowdsec-status' => $result,
                ]
            );

            break;
        default:
            echo $default;
    }
} else {
    echo $default;
}
