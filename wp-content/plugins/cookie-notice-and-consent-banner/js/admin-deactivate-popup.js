(function ($) {
    'use strict';

    var CNCBAdminDialogApp = {
        initBtnText: 'Skip & Deactivate',
        submitBtnText: 'Send Feedback & Deactivate',
        cancelBtnText: 'Cancel',
        progressBtnText: 'Sending Feedback...',
        cacheElements: function cacheElements() {
            this.cache = {
                $deactivateLink: $('#the-list').find('[data-slug="cookie-notice-and-consent-banner"] span.deactivate a'),
                $dialogRadio: $('.cncb-deactivate-feedback-dialog-input'),
                $dialogInput: $('.cncb-feedback-text'),
                $dialogForm: $('#cncb-deactivate-feedback-dialog-form'),
                $dialogSubmitButton: $('.cncb-dialog-submit')
            };
        },
        setCookie: function setCookie(name,value,days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        },
        getCookie: function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        },
        bindEvents: function bindEvents() {
            var self = this;
            self.cache.$deactivateLink.on('click', function (event) {
                var show_banner = self.getCookie('cncb_show_deactivate_popup');

                if(!show_banner) {
                    event.preventDefault();
                    self.clearModalState();
                    self.getModal().dialog('open');
                    self.setCookie('cncb_show_deactivate_popup', 'true');
                }
            });
            self.cache.$dialogRadio.on('change', function () {
                $('.cncb-dialog-submit').find('.ui-button-text').text(self.submitBtnText);
            });
        },
        deactivate: function deactivate() {
            location.href = this.cache.$deactivateLink.attr('href');
        },
        initModal: function initModal() {
            var self = this,
                modal;

            self.getModal = function () {
                modal = $( "#cncb-deactivate-feedback-dialog-wrapper" ).dialog({
                    dialogClass: 'no-close',
                    autoOpen: false,
                    height: 480,
                    width: 590,
                    modal: true,
                    resizable: false,
                    buttons: [
                        {
                            id: 'cncb-dialog-submit',
                            class: 'cncb-dialog-submit',
                            text: self.initBtnText,
                            click:  function () {
                                var isChecked = $('.cncb-deactivate-feedback-dialog-input:checked').length;
                                if (isChecked) {
                                    $('.cncb-dialog-submit').find('.ui-button-text').text(self.progressBtnText);
                                    var formData = self.cache.$dialogForm.serialize();
                                    $.post(ajaxurl, formData, self.deactivate.bind(self));
                                } else {
                                    self.deactivate();
                                }
                            },
                        },
                        {
                            id: 'cncb-dialog-skip',
                            class: 'cncb-dialog-skip',
                            text: self.cancelBtnText,
                            click:  function () {
                                self.getModal().dialog('close');
                            },
                        }
                    ]
                });
                return modal;
            };
        },
        clearModalState: function clearModalState() {
            this.cache.$dialogRadio.removeAttr('checked');
            this.cache.$dialogInput.val('');
        },
        init: function init() {
            this.initModal();
            this.cacheElements();
            this.bindEvents();
        }
    };
    $(function () {
        CNCBAdminDialogApp.init();
    });
})(jQuery);
