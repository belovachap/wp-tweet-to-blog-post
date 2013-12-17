jQuery(document).ready(function() {
	ratio_mode();
	
	jQuery('#dg_tw_time_selected').on('change',function() {
		ratio_mode();
	});
	
	jQuery('#dg_tw_add_title').on('keypress',function(e) {
	    if(e.which == 13) {
			e.preventDefault();
	    	dg_tw_add_query();
	    }
	});
	
	jQuery('#dg_tw_add_element').click(function() {
		dg_tw_add_query();
	});
	
	jQuery('#dg_tw_elements_selected').on('click','.dg_tw_button_remove',function(event) {
		event.preventDefault();
		jQuery(this).parent().remove();
	});
	
	jQuery('#dg_tw_import_now').on('click',function(event) {
		jQuery('#dg_tw_import_now').text('Running...');
		
		jQuery.post(ajaxurl, {action:'dg_tw_event_start'}, function(response) {
			jQuery('#dg_tw_import_now').text('Import Tweets now!');
		});
	});
	
	jQuery('.nav-tab-wrapper-dgtw .nav-tab').on('click',function(event) {
		var item_to_show = '.dg_tw_tabs' + jQuery(this).data('item');

		jQuery(this).siblings().removeClass('nav-tab-active');
		jQuery(this).addClass("nav-tab-active");
		
		jQuery(item_to_show).siblings().css('display','none');
		jQuery(item_to_show).css('display','block');
	});
});

function ratio_mode() {
	var dg_tw_ratio_value = jQuery('#dg_tw_time_selected').val();
	
	if(dg_tw_ratio_value == 'dg_tw_weekly' || dg_tw_ratio_value == 'dg_tw_monthly') {
		jQuery('#dg_tw_cycle_selectors').show();
	} else {
		jQuery('#dg_tw_cycle_selectors').hide();
	}
	
	return true;
}

function dg_tw_add_query() {
	if(jQuery('#dg_tw_add_title').val().length != 0) {
		// Find the next available index
		var index = -1;
		jQuery('.js-converter-container').each(function() {
			index = Math.max(index, jQuery(this).data('index'));
		});
		index += 1;

		// Build new converter container
		var $container = jQuery('<p style="text-align:left;padding:5px;" class="js-converter-container">');
		$container.data('index', index);

		var input_name_prefix = 'dg_tw_item_query[' + index + ']';
		var query_value = jQuery('#dg_tw_add_title').val();

		var $deleteButton = jQuery('<input class="button-primary dg_tw_button_remove" type="button" name="delete" value="Delete">');
		var $converterIDInput = jQuery('<input type="hidden" name="' + input_name_prefix + '[id]" value="">');
		var $queryValueInput = jQuery('<input type="text" size="20" class="regular-text" name="' + input_name_prefix + '[value]" value="' + query_value + '">');
		var $tagLabel = jQuery('<span>&nbsp;&nbsp;&nbsp;tag:&nbsp;</span>');
		var $tagInput = jQuery('<input type="text" size="20" name="' + input_name_prefix + '[tag]" value="' + query_value +'">');
		var $methodLabel = jQuery('<span>&nbsp;&nbsp;&nbsp;method:&nbsp;</span>');
		var $methodInput = jQuery('#dg_tw_query_method').clone().attr('name', input_name_prefix + '[method]').removeAttr('id');
		$methodInput.val(jQuery('#dg_tw_query_method').val());

		$container.append(
			$deleteButton,
			$converterIDInput,
			$queryValueInput,
			$tagLabel,
			$tagInput,
			$methodLabel,
			$methodInput
		);

		jQuery('#dg_tw_elements_selected').append($container);
		jQuery('#dg_tw_add_title').attr('value','')
	} else {
		alert('Fill the query string box!');
	}
}
