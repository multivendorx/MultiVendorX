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


function wcmp_setup_commission( ) {
    wc_deprecated_function( 'wcmp_setup_commission )', '4.0.0', 'mvx_setup_commission');
    return mvx_setup_commission();
}

function wcmp_setup_payments() {
    wc_deprecated_function( 'wcmp_setup_payments', '4.0.0', 'mvx_setup_payments');
    return mvx_setup_payments();
}
function wcmp_setup_capability() {
    wc_deprecated_function( 'wcmp_setup_capability )', '4.0.0', 'mvx_setup_capability');
    return mvx_setup_capability();
}
function wcmp_setup_ready() {
    wc_deprecated_function( 'wcmp_setup_ready', '4.0.0', 'mvx_setup_ready');
    return mvx_setup_ready();
}
function wcmp_setup_store_save() {
    wc_deprecated_function( 'wcmp_setup_store_save', '4.0.0', 'mvx_setup_store_save');
    return mvx_setup_store_save();
}
function wcmp_migration_introduction( ) {
    wc_deprecated_function( 'wcmp_migration_introduction )', '4.0.0', 'mvx_migration_introduction');
    return mvx_migration_introduction();
}

function wcmp_setup_commission_save() {
    wc_deprecated_function( 'wcmp_setup_commission_save', '4.0.0', 'mvx_setup_commission_save');
    return mvx_setup_commission_save();
}
function wcmp_setup_payments_save() {
    wc_deprecated_function( 'wcmp_setup_payments_save )', '4.0.0', 'mvx_setup_payments_save');
    return mvx_setup_payments_save();
}
function wcmp_setup_capability_save() {
    wc_deprecated_function( 'wcmp_setup_capability_save', '4.0.0', 'mvx_setup_capability_save');
    return mvx_setup_capability_save();
}
function wcmp_migration_store_process() {
    wc_deprecated_function( 'wcmp_migration_store_process', '4.0.0', 'mvx_migration_store_process');
    return mvx_migration_store_process();
}


function wcmp_approve_dismiss_pending_transaction($request){
    wc_deprecated_function( 'wcmp_approve_dismiss_pending_transaction', '4.0.0', 'mvx_approve_dismiss_pending_transaction');
    return mvx_approve_dismiss_pending_transaction($request);
}
function wcmp_modules_callback() {
    wc_deprecated_function( 'wcmp_modules_callback', '4.0.0', 'mvx_modules_callback');
    return mvx_modules_callback();
}
function wcmp_rest_routes_react_module() {
    wc_deprecated_function( 'wcmp_rest_routes_react_module )', '4.0.0', 'mvx_rest_routes_react_module');
    return mvx_rest_routes_react_module();
}
function wcmp_list_of_all_tabs() {
    wc_deprecated_function( 'wcmp_list_of_all_tabs', '4.0.0', 'mvx_list_of_all_tabs');
    return mvx_list_of_all_tabs();
}
function wcmp_list_of_bulk_change_status_question($request) {
    wc_deprecated_function( 'wcmp_list_of_bulk_change_status_question', '4.0.0', 'mvx_list_of_bulk_change_status_question');
    return mvx_list_of_bulk_change_status_question($request);
}


function mvx_list_vendor_roles_data($request) {
    wc_deprecated_function( 'mvx_list_vendor_roles_data', '4.0.0', 'mvx_list_vendor_roles_data');
    return mvx_list_vendor_roles_data($request);
}
function mvx_list_vendor_application_data($request) {
    wc_deprecated_function( 'mvx_list_vendor_application_data', '4.0.0', 'mvx_list_vendor_application_data');
    return mvx_list_vendor_application_data($request);
}
function mvx_active_suspend_vendor($request) {
    wc_deprecated_function( 'mvx_active_suspend_vendor', '4.0.0', 'mvx_active_suspend_vendor');
    return mvx_active_suspend_vendor($request);
}
function mvx_get_as_per_module_status($request) {
    wc_deprecated_function( 'mvx_get_as_per_module_status', '4.0.0', 'mvx_get_as_per_module_status');
    return mvx_get_as_per_module_status($request);
}
function mvx_modules_count() {
    wc_deprecated_function( 'mvx_modules_count', '4.0.0', 'mvx_modules_count');
    return mvx_modules_count();
}