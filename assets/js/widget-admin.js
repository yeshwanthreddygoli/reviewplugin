/* jshint ignore:start */
(function ($, w) {
	$(document).ready(function () {
        initAll();
	});

    function initAll() {
		var widget_selector = $('.widget');
		if (widget_selector.length > 0) {
			widget_selector.each(function () {
				var id = $(this).attr("id")
				for(name in rp_widget.names){
					if (id.indexOf(rp_widget.names[name]) !== -1) {
						toggleCustomFields(true, id);
					}
				}

			});
		} else {
			toggleCustomFields(true, "wpcontent");
		}


        $('.rp-range-slider').each(function(){
            $(this).slider({
                range   : true,
                step    : 1,
                min     : parseInt($(this).attr('data-rp-min')),
                max     : parseInt($(this).attr('data-rp-max')),
                values  : JSON.parse('[' + $(this).attr('data-rp-value') + ']'),
                slide   : function( event, ui ) {
                    var $desc = $('#' + $(this).attr('data-rp-desc'));
                    $desc.find('input').val(ui.values[0] + ',' + ui.values[1]);
                    $desc.find('span.rp-range-min').html(Math.abs(ui.values[0]));
                    $desc.find('span.rp-range-max').html(Math.abs(ui.values[1]));
                }
            });
        });
	
        $(document).on('widget-updated widget-added', function (e, widget) {
            initEvents(widget);
        });

        initEvents(null);
    }

    function initEvents(widget) {
        if(widget){
            widget.find( '.chosen-container' ).remove();
            widget.find('select.rp-chosen').chosen({
                width               : '100%',
                search_contains     : true
            });
            widget.find('.rp-post-types').on('change', function(evt, params) {
                get_categories(params, $(this), $('#' + $(this).attr('data-rp-cat-combo')));
            });
            widget.find('.rp-post-type').on('change', function(evt, params) {
                get_taxonomies(params, $(this), $('#' + $(this).attr('data-rp-cat-combo')));
            });
        }else{
            $('select.rp-chosen').chosen({
                width               : '100%',
                search_contains     : true
            });
            $('.rp-post-types').on('change', function(evt, params) {
                get_categories(params, $(this), $('#' + $(this).attr('data-rp-cat-combo')));
            });
            $('.rp-post-type').on('change', function(evt, params) {
                get_taxonomies(params, $(this), $('#' + $(this).attr('data-rp-cat-combo')));
            });
        }

        $('.rp-datepicker').each(function(){
            $(this).datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true
            });
        });


    }

    function get_taxonomies(params, types, categories){
        if(params.selected){
            $('.rp-cat-spinner').css('visibility', 'visible').show();
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    action  : 'get_taxonomies',
                    nonce   : w.ajax.nonce,
                    type    : params.selected
                },
                success : function(data){
                    categories.empty();
                    if(data.data && data.data.categories){
                        var $all = '';
                        $.each(data.data.categories, function(tax, arr){
                            var $group = '<optgroup label="' + tax + '">';
                            $.each(arr, function(slug, name){
                                $group += '<option value="' + slug + '">' + name + '</option>';
                            });
                            $group += '</optgroup>';
                            $all += $group;
                        });
                        categories.append($all);
                    }
                    categories.trigger("chosen:updated");
                    $('.rp-cat-spinner').css('visibility', 'hidden').hide();
                }
            });
        }
    }

    function get_categories(params, types, categories){
        if(params.selected){
            $('.rp-cat-spinner').css('visibility', 'visible').show();
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    action  : 'get_categories',
                    nonce   : w.ajax.nonce,
                    type    : params.selected
                },
                success : function(data){
                    if(data.data.categories){
                        var $group = '<optgroup label="' + types.find('option[value="' + params.selected + '"]').text() + '">';
                        $.each(data.data.categories, function(slug, name){
                            $group += '<option value="' + slug + '">' + name + '</option>';
                        });
                        $group += '</optgroup>';
                        categories.append($group);
                        categories.trigger("chosen:updated");
                    }
                    $('.rp-cat-spinner').css('visibility', 'hidden').hide();
                }
            });
        }else{
            categories.find('optgroup[label="' + types.find('option[value="' + params.deselected + '"]').text() + '"]').remove();
            categories.trigger("chosen:updated");
        }
    }


	function toggleCustomFields(deflt, widgetID) {
		var val = getWidgetStyle(widgetID);
		if (val === "default.php") {
			$("#" + widgetID).find(".rp-customField").hide();
		} else {
			$("#" + widgetID).find(".rp-customField").show();
		}

		addListeners(widgetID);
	}

	$(document).on('widget-updated widget-added', function (e, w) {
		toggleCustomFields(true, w[0]["id"]);
	});

	function addListeners(widgetID) {
		var widget = $("#" + widgetID);
		widget.find("input.rp-stylestyle").on("click", function (e) {
			toggleCustomFields(false, widgetID);
		});
		widget.find("label.rp-stylestyle").hover(function (e) {
			var img = $("#" + $(this).attr("for") + "img");
			img.show();
			img.css('position', 'absolute');
			img.css('width', '100%');
		}, function (e) {
			$("#" + $(this).attr("for") + "img").hide();
		});
	}

	function getWidgetStyle(id) {
		var name = $("#" + id).find("input:radio.rp-stylestyle").attr("name");
		return $("input:radio[name='" + name + "']:checked").val();
	}

})(jQuery, rp_widget);
