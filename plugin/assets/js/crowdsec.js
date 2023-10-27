/* global moment, $ */
/* exported CrowdSec */
/* eslint no-undef: "error" */
/* eslint semi: "error" */

const CrowdSec = (function () {
    'use strict';

    const api_url =  '/cgi/crowdsec/api.php';

    function _yesno2html (val) {
        if (val) {
            return '<i class="fa fa-check text-success"></i>';
        } else {
            return '<i class="fa fa-times text-danger"></i>';
        }
    }

    function initService() {
        
        $.ajax({
            url: window.COMMON.securityToken + api_url,
            cache: false,
            dataType: 'json',
            data: {action: 'services-status'},
            type: 'POST',
            method: 'POST',
            success: function(data) {
                let crowdsecStatus = data['crowdsec-status'];
                $('#crowdsec-status').html(crowdsecStatus);
            }
        })
    }


    return {
        initService: initService
    };
}());
