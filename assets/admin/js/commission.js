jQuery(document).ready(function($) {
    $( '.mvx-commission-amount a.edit_commission_amount' ).click( function( e ) {
        e.preventDefault();
        $('.commission-amount-view').toggle();
        $('.commission-amount-edit').toggle();
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
});
