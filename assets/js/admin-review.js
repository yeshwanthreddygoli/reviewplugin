/* jshint ignore:start */
(function($){
    
    $( document ).ready(function(){

        var meta_image_frame;

        $( '#rp-editor-image-button' ).click(function(e){

            e.preventDefault();

            if ( meta_image_frame ) {
                wp.media.frame.open();
                return;
            }
            var mtitle = editor_vars.image_title ;
            var mbutton = editor_vars.image_button;

            meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                title: mtitle,
                button: { text:  mbutton },
                library: { type: 'image' }
            });

            meta_image_frame.on('select', function(){

                var media_attachment = meta_image_frame.state().get( 'selection' ).first().toJSON();

                $( '#rp-editor-image' ).val( media_attachment.url );
            });

            wp.media.frame.open();
        });

        $( 'input:radio[name="rp-review-status"]' ).change(function(){
            var value = $( this ).val();
            if (value === "yes") {

                $( "#rp-meta-yes" ).show();
                $( "#rp-meta-no" ).show();
            } else {
                $( "#rp-meta-yes" ).hide();
                $( "#rp-meta-no" ).hide();
            }
        });

        $( '#rp-editor-new-link' ).click(function(e){
            e.preventDefault();
            $( '.hidden_fields' ).show();
            $( this ).hide();
            return false;
        });

        $type = $('#rp-editor-review-type').val();
        if($type !== ''){
            populate_schema($type);
        }

        $('#rp-editor-review-type').on('change', function(e){
            $type = $(this).val();
            populate_schema($type);
        });

        $('.rp-review-type-fields-toggle a').on('click', function(e){
            e.preventDefault();
            if($('.rp-review-type-fields').hasClass('hide')){
                $('.rp-review-type-fields').removeClass('hide');
                $('.rp-review-type-fields-toggle span').html('-');
            }else{
                $('.rp-review-type-fields').addClass('hide');
                $('.rp-review-type-fields-toggle span').html('+');
            }
        });

    });

    function populate_schema($type){
        $json = $values = null;
        $data = $('#rp-review-type-fields-template').attr('data-json');
        if(typeof $data !== 'undefined'){
            $json = JSON.parse($data);
        }
        $data = $('#rp-review-type-fields-template').attr('data-custom-fields');
        if(typeof $data !== 'undefined'){
           $values = JSON.parse($data);
        }
        $saved_type = $('#rp-review-type-fields-template').attr('data-type');
        $template = $('#rp-review-type-fields-template').html();
        $html = '';
        if($json !== null){
            $.each($json[$type], function(name, data){
                $value = '';
                if($type === $saved_type && $values !== null){
                    $value = $values[name];
                }
                $desc = data.desc.replace(/<a /g, '<a target="blank" ');
                $html += $template.replace(/#name#/g, name).replace(/#desc#/, $desc).replace(/#value#/g, $value);
            });
            $('.rp-review-type-fields').empty().append($html);
        }
    }

})(jQuery);