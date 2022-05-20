<?php

/**
 * @deprecated 4.0.0
 * wc_deprecated_function(new, version, old)
 */
function is_user_wcmp_vendor( $user ) {
	wc_deprecated_function( 'is_user_wcmp_vendor', '4.0.0', 'is_user_mvx_vendor' );
	return is_user_mvx_vendor( $user );
}