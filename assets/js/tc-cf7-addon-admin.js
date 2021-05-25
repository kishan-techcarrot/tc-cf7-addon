var tcCF7AddonAdmin = function () {

    return {
        init: function () 
        {
            jQuery( 'body' ).on('click', '#TB_window #TB_ajaxContent .nav-tab-wrapper a', tcCF7AddonAdmin.actions.hideShowTabs);
        },

    	actions:
        {
            hideShowTabs: function (event) 
            {
                jQuery('body').find('#TB_window #TB_ajaxContent .nav-tab-wrapper a').removeClass('nav-tab-active');
                jQuery('body').find('#TB_window #TB_ajaxContent .settings-panel').hide();

                var id = jQuery(event.target).attr('href'); 

                jQuery(event.target).addClass('nav-tab-active');
                jQuery('body').find('#TB_window #TB_ajaxContent ' + id).show();
            },
        }
    }
};

tcCF7AddonAdmin = tcCF7AddonAdmin();
jQuery(document).ready(function ($) {
    tcCF7AddonAdmin.init();
});