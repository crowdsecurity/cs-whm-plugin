/* exported CrowdSecAcquis */

const CrowdSecAcquis = (function () {
    'use strict';

    const api_url = '/cgi/crowdsec/endpoints/api.php';

    function keepOnlySource(source) {
        const form = $('form[name="acquisition"]');

        const fields = form.find(':input');

        fields.each(function () {
            let fieldName = $(this).attr('name');
            const matches = fieldName.match(/\[([^)]+)\]/);
            if (matches && matches.length > 1) {
                fieldName = matches[1];
            }

            const formGroup = $(this).closest('.form-group');
            if (!(fieldName && (fieldName.startsWith('save') || fieldName.startsWith('filepath') || fieldName.startsWith('common_') || fieldName.startsWith(`${source}_`)))) {

                formGroup.hide();
                formGroup.find(':input').prop('disabled', true);
            } else {
                formGroup.show();
                formGroup.find(':input').prop('disabled', false);
            }
        });
    }


    function initForm() {

        const source = $("select[name='acquisition[common_source]']").val();
        keepOnlySource(source);


        $("select[name='acquisition[common_source]']").change(function () {
            keepOnlySource(this.value);
        });
    }

    function initList() {

        // Delete acquisition
        $("button[name='delete']").click(function () {
            const acquisId = this.value;
            const dialogId = '#delete-dialog-confirm';
            $('.alert').hide();
            $(dialogId).dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    "Delete": function () {
                        $(".delete-loading").show();
                        $.ajax({
                            url: window.COMMON.securityToken + api_url,
                            cache: false,
                            dataType: 'json',
                            data: {
                                action: "delete-acquisition",
                                hash: acquisId
                            },
                            type: 'POST',
                            method: 'POST',
                            success: function (result) {
                                if (result.error) {
                                    $('.alert-danger > ul > li').text(result.error);
                                    $('.alert-danger').show();
                                }
                                else {
                                    $(`#acquisition-${acquisId}`).remove();
                                    $('.alert-success > ul > li').text('Acquisition successfully deleted.');
                                    $('.alert-success').show();
                                    $('.alert-info > ul > li').text('Please restart the CrowdSec service to apply changes.');
                                    $('.alert-info').show();
                                }
                            },
                            error: function () {
                                $('.alert-danger > ul > li').text('Something went wrong while deleting.')
                                $('.alert-danger').show();
                            },
                            complete: function () {
                                $(".delete-loading").hide();
                                $(dialogId).dialog("close");
                                $('html, body').animate({
                                    scrollTop: $("#pageContainer").offset().top
                                }, 500);
                                $('#refresh-crowdsec-btn').click();
                            }
                        })

                    },
                    Cancel: function () {
                        $(this).dialog("close");
                    }
                }
            });
        });

        // Warning message for unread files
        $('.unread-file').hover(
            function () {
                let warningClass = 'alert-warning';
                if ($(this).hasClass('alert-danger')) {
                    warningClass = 'alert-danger';
                }
                const warningMessage = warningClass === 'alert-warning' ?
                    'Notice: This file has not been read yet. The CrowdSec service was restarted less than 5 minutes ago.' :
                    'Warning: This file has not been read yet and the CrowdSec service was restarted more than 5 minutes ago.';
                const warningDiv = $(`<div class="warning-message ${warningClass}">${warningMessage}</div>`);
                $(this).append(warningDiv);
            },
            function () {
                // Mouse out
                $(this).find('.warning-message').remove();
            }
        );

    }


    return {
        initForm: initForm,
        initList: initList,
    };
}());
