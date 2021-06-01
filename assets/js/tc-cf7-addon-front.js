var tcCF7AddonFront = function () {

    return {
        init: function () 
        {
            

            if( jQuery('form.wpcf7-form').length > 0 )
            {
                //document.addEventListener('wpcf7mailfailed', tcCF7AddonFront.actions.tcCf7MailFailed);
                //document.addEventListener('wpcf7mailsent ', tcCF7AddonFront.actions.tcCf7MailSent);
            }
        },

    	actions:
        {
            tcCf7MailFailed: function(event) 
            {
                console.log(event);

                var fields = event.detail.inputs;
                jQuery.ajax({
                    url: tc_cf7_addon.ajax_url,
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        action: 'tc_cf7_addon_update_mail_status',
                        form_id: event.detail.contactFormId,
                        mail_status: 'failed',
                        security: tc_cf7_addon.tc_cf7_addon_security,
                    },
                    success: function (responce)
                    {
                    }
                });
            },

            tcCf7MailSent: function(event) 
            {
                
            },
        }
    }
};

tcCF7AddonFront = tcCF7AddonFront();
jQuery(document).ready(function ($) {
    tcCF7AddonFront.init();
});