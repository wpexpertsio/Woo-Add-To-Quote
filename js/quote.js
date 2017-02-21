jQuery(document).ready(
    function($) {
        $(document).on('change','.variations select,.variations-table select,.variation_form_section #color,.variation_form_section #pa_colors', function() {
            setTimeout(function() { _display_hide() } ,500);
            function _display_hide() {
				var attr = $('.single_variation_wrap').find('.single_add_to_cart_button').attr('disabled');
				if($('.single_variation_wrap').css('display') == 'block' && (typeof attr === typeof undefined || attr === false)) {
                    $('#_add_to_quote_form_wrapper').find('._add_to_quote_submit').removeClass('_hide');
                }
                else if($('.single_variation_wrap').css('display') == 'none' || (typeof attr !== typeof undefined || attr !== false)) {
                    $('#_add_to_quote_form_wrapper').find('._add_to_quote_submit').addClass('_hide');
                }
            }
        });
		_remove_th_on_responsive();
		change_size();
		$(window).resize(
			function() {
				_remove_th_on_responsive();
				change_size();
			}
		);
		
		function _remove_th_on_responsive() {
			if($(window).width() <= 768){
				$('.quote table thead th.product-remove').text(' ');
				$('.quote table thead th.product-thumbnail').text(' ');
			}
			else {
				$('.quote table thead th.product-remove').text('Remove');
				$('.quote table thead th.product-thumbnail').text('Product Image');
			}
		}

		$('#_email_quote_trigger').click(
			function() {
				$('#_send_quote_email_')[0].click();
			}
		);

		$('#send_trigger').click(
			function() {
				var $toSend = $('#_to_send_email').val();
				$('._to_send_email').val($toSend);
				$('.quote_data_wrapper ._submit').click();
			}
		);

		$('#waqt_user_quote_detail').find('._tab_menu_option').click(
			function() {

				$(this).parents('._table_content_wrapper').siblings().find('._tab_accordian_panel').removeClass('active');
				$(this).parents('._table_content_wrapper').siblings().find('._tab_accordian_panel').slideUp();
				if($(this).parents('._table_content_wrapper').find('._tab_accordian_panel').hasClass('active')) {
					$(this).parents('._table_content_wrapper').find('._tab_accordian_panel').removeClass('active');
					$(this).parents('._table_content_wrapper').find('._tab_accordian_panel').slideUp();
				}
				else {
					$(this).parents('._table_content_wrapper').find('._tab_accordian_panel').addClass('active');
					$(this).parents('._table_content_wrapper').find('._tab_accordian_panel').slideDown();
				}
			}
		);

		function change_size(){
			var $target = $('._quoteall_buttons_wrapper');
			var $buttons_wrapper_width = $target.width();
			var $window_width = $(window).width();

			if($buttons_wrapper_width < 550 && $window_width < 950){
				$target.addClass('small_width');
			}
			else if($buttons_wrapper_width > 550 && $window_width > 950) {
				$target.removeClass('small_width');
			}
		}
    }
);