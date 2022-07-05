<?php

/**
 * @deprecated 4.0.0
 * wc_deprecated_function(new, version, old)
 */
function is_user_wcmp_vendor( $user ) {
	wc_deprecated_function( 'is_user_wcmp_vendor', '4.0.0', 'is_user_mvx_vendor' );
	return is_user_mvx_vendor( $user );
}
function wcmp_delete_woocomerce_transient_redirect_to_mvx_setup( ) {
    wc_deprecated_function( 'wcmp_delete_woocomerce_transient_redirect_to_mvx_setup )', '4.0.0', 'mvx_delete_woocomerce_transient_redirect_to_mvx_setup');
    return mvx_delete_woocomerce_transient_redirect_to_mvx_setup();
}

function wcmp_admin_notice() {
    wc_deprecated_function( 'wcmp_admin_notice', '4.0.0', 'mvx_admin_notice');
    return mvx_admin_notice();
}
function wcmp_namespace_approve() {
    wc_deprecated_function( 'wcmp_namespace_approve( $rates, $package )', '4.0.0', 'mvx_namespace_approve');
    return mvx_namespace_approve();
}
function wcmp_setup_introduction() {
    wc_deprecated_function( 'wcmp_setup_introduction', '4.0.0', 'mvx_setup_introduction');
    return mvx_setup_introduction();
}
function wcmp_setup_store() {
    wc_deprecated_function( 'wcmp_setup_store', '4.0.0', 'mvx_setup_store');
    return mvx_setup_store();
}