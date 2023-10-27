/* exported CrowdSecServices */

const CrowdSecServices = (function () {
    'use strict';

    const api_url = '/cgi/crowdsec/endpoints/api.php';
    const startButton = $('#restart-crowdsec-btn');
    const refreshButton = $('#refresh-crowdsec-btn');
    const restartId = $('#restart-needed');


    function getServiceStatus() {
        $.ajax({
            url: window.COMMON.securityToken + api_url,
            cache: false,
            dataType: 'json',
            data: { action: 'services-status' },
            type: 'POST',
            method: 'POST',
            success: function (data) {
                let crowdsecStatus = data['crowdsec-status'] === 'running'
                crowdsecStatus = _yesno2html(crowdsecStatus);
                $('#crowdsec-status').html(crowdsecStatus);
            }
        })
    }

    function initServices() {

        getServiceStatus();

        startButton.click(function () {
            startButton.css('display', 'flex')
            this.innerHTML = '<div class="loader"></div>';
            $.ajax({
                url: window.COMMON.securityToken + api_url,
                cache: false,
                dataType: 'json',
                data: { action: 'crowdsec-restart' },
                type: 'POST',
                method: 'POST',
                complete: function () {
                    getServiceStatus();
                    startButton.html('Restart CrowdSec service');
                    restartId.hide();
                }
            })
        });

        refreshButton.click(function () {
            $('#crowdsec-status').html('<i class="fa fa-spinner fa-spin"></i>');
            getServiceStatus();
        });
    }

    return {
        initServices: initServices
    };
}());
