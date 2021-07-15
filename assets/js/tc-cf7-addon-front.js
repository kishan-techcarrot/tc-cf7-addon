var tcCF7AddonFront = function () {

    return {
        init: function () 
        {
            if (jQuery('.wpcf7').length > 0)
            {
                var wpcf7Elm = document.querySelector( '.wpcf7' );
                wpcf7Elm.addEventListener( 'wpcf7mailsent', function( event ) {
                  tcCF7AddonFront.actions.tcCf7MailSent(event);
                }, false );
            }
        },

        actions:
        {
            tcCf7MailSent: function(event) 
            {
                var fields = event.detail.inputs;
                jQuery.ajax({
                    url: tc_cf7_addon.ajax_url,
                    type: 'POST',
                    dataType: 'HTML',
                    data: {
                        action: 'tc_cf7_addon_mail_sent',
                        cf7_id: event.detail.contactFormId,
                        security: tc_cf7_addon.tc_cf7_addon_security,
                    },
                    success: function (responce)
                    {
                        if(responce != '')
                        {
                            window.location.replace(responce);
                        }
                    }
                });
            },
        }
    }
};

tcCF7AddonFront = tcCF7AddonFront();
jQuery(document).ready(function ($) {
    tcCF7AddonFront.init();
});