(function ($) {
	$(document).ready(function () {

		const requestTypes = [ 'refund', 'return' ];

		requestTypes.forEach(function (type) {

			const popup = $( '#multivendorx-myac-order-' + type + '-wrap' );
			const button = $( '#cust-request-' + type + '-btn' );

			popup.hide();
			popup.find( '.cust-rr-other' ).hide();

			button.on( 'click', function (e) {
				e.preventDefault();
				popup.slideToggle();
			});

			popup.find( '.popup-close' ).on( 'click', function (e) {
				e.preventDefault();
				popup.fadeOut();
			});

			popup.find( 'input[name="' + type + '_reason_option"]' ).on(
				'change',
				function () {
					if ( $( this ).val() === 'others' ) {
						popup.find( '.cust-rr-other' ).show();
					} else {
						popup.find( '.cust-rr-other' ).hide();
					}
				}
			);
		});
	});
})(jQuery);