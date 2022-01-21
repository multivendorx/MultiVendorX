/* global ajaxurl */

jQuery(document).ready(function($) {
	$('select.ajax_chosen_select_vendor').ajaxChosen({
		method : 'GET',
		url : ajaxurl,
		dataType : 'json',
		afterTypeDelay : 100,
		minTermLength : 1,
		data : {
			action : 'woocommerce_json_search_vendors',
		}
	}, function(data) {

		var terms = {};

		$.each(data, function(i, val) {
			terms[i] = val;
		});

		return terms;
	});
	
	$('select.ajax_chosen_select_products_and_variations').ajaxChosen({
			method: 	'GET',
			url: 		ajaxurl,
			dataType: 	'json',
			afterTypeDelay: 100,
			data:		{
				action: 'woocommerce_json_search_products_and_variations',
				security: dc_vendor_object.security
			}
	}, function (data) {
	
		var terms = {};
			$.each(data, function (i, val) {
					terms[i] = val;
			});
			return terms;
	});
	
	$( '.delete_vendor_data' ).click(function() {
		var unassign_vendor = {
			action: 'unassign_vendor',
			'product_id': unassign_vendors_data.current_product_id,
		};
		
		$.post( ajaxurl, unassign_vendor, function(response) {
			$('.chosen-single span').html('Choose a vendor');
			$('#choose_vendor_ajax option:selected').remove();
			$('.input-commision').val('');
			$('#post_author').val(unassign_vendors_data.current_user_id);
			$('._product_vendors_commission_percentage input').val('');
			$('._product_vendors_commission_fixed_per_qty input').val('');
			$('._product_vendors_commission_percentage input').val('');
			$('._product_vendors_commission_fixed_per_qty input').val('');
		});
	
	});
        
        if ( $.isFunction($.fn.singleProductMulipleVendor) ) {
            $('input[name=post_title]').singleProductMulipleVendor({
                ajaxurl : ajaxurl
            });
        }
		
}); 