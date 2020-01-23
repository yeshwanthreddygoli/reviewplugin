/* global jQuery */

(function($){

    $(document).ready(function(){
        onReady();
    });

    function onReady() {
        $('.rp-review-type').accordion({
            heightStyle: 'content',
            collapsible: true,
            active: false,
            icons: false
        });
    }

})(jQuery);