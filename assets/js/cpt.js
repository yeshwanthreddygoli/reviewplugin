/* global jQuery */
/* global rp */

(function($, rp){

    $(document).ready(function(){
        onReady();
    });

    $(window).load(function(){
        onLoad();
    });

    function onReady() {
        // check the is review radio button.
        $('#rp-review-yes').attr('checked', 'checked');
        // hide the radio button settings.
        $('p.rp-active').hide();
        // auto show the review settings.
        $('.rp-review-editor').show();
        // hide the product name row.
        $('#rp-editor-product-name').parent().hide();
        // change the title placeholder.
        $('#title-prompt-text').html(rp.i10n.title_placeholder);
    }

    function onLoad() {
    }

})(jQuery, rp);