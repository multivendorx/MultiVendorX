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
function wcmp_list_vendor_roles_data($request) {
    wc_deprecated_function( 'wcmp_list_vendor_roles_data', '4.0.0', 'mvx_list_vendor_roles_data');
    return mvx_list_vendor_roles_data($request);
}
function wcmp_list_vendor_application_data($request) {
    wc_deprecated_function( 'wcmp_list_vendor_application_data', '4.0.0', 'mvx_list_vendor_application_data');
    return mvx_list_vendor_application_data($request);
}
function wcmp_active_suspend_vendor($request) {
    wc_deprecated_function( 'wcmp_active_suspend_vendor', '4.0.0', 'mvx_active_suspend_vendor');
    return mvx_active_suspend_vendor($request);
}
function wcmp_get_as_per_module_status($request) {
    wc_deprecated_function( 'wcmp_get_as_per_module_status', '4.0.0', 'mvx_get_as_per_module_status');
    return mvx_get_as_per_module_status($request);
}
function wcmp_modules_count() {
    wc_deprecated_function( 'wcmp_modules_count', '4.0.0', 'mvx_modules_count');
    return mvx_modules_count();
}
function wcmp_fetch_all_modules_data() {
    wc_deprecated_function( 'wcmp_fetch_all_modules_data', '4.0.0', 'mvx_fetch_all_modules_data');
    return mvx_fetch_all_modules_data();
}
function wcmp_report_abuse_delete($request) {
    wc_deprecated_function( 'wcmp_report_abuse_delete', '4.0.0', 'mvx_report_abuse_delete');
    return mvx_report_abuse_delete($request);
}
function wcmp_report_abuse_details($request) {
    wc_deprecated_function( 'wcmp_report_abuse_details', '4.0.0', 'mvx_report_abuse_details');
    return mvx_report_abuse_details($request);
}
function wcmp_fetch_system_info() {
    wc_deprecated_function( 'wcmp_fetch_system_info', '4.0.0', 'mvx_fetch_system_info');
    return mvx_fetch_system_info();
}
function wcmp_find_module_name_by_id($module_id = '') {
    wc_deprecated_function( 'wcmp_find_module_name_by_id', '4.0.0', 'mvx_find_module_name_by_id');
    return mvx_find_module_name_by_id($module_id = '');
}
function wcmp_system_info_copy_data() {
    wc_deprecated_function( 'wcmp_system_info_copy_data', '4.0.0', 'mvx_system_info_copy_data');
    return mvx_system_info_copy_data();
}
function wcmp_tools_funtion($request) {
    wc_deprecated_function( 'wcmp_tools_funtion', '4.0.0', 'mvx_tools_funtion');
    return mvx_tools_funtion($request);
}
function wcmp_search_review($request) {
    wc_deprecated_function( 'wcmp_search_review', '4.0.0', 'mvx_search_review');
    return mvx_search_review($request);
}
function wcmp_delete_review($request) {
    wc_deprecated_function( 'wcmp_delete_review', '4.0.0', 'mvx_delete_review');
    return mvx_delete_review($request);
}
function wcmp_search_question_ans($request) {
    wc_deprecated_function( 'wcmp_search_question_ans', '4.0.0', 'mvx_search_question_ans');
    return mvx_search_question_ans($request);
}
function wcmp_fetch_all_settings_for_searching($request) {
    wc_deprecated_function( 'wcmp_fetch_all_settings_for_searching', '4.0.0', 'mvx_fetch_all_settings_for_searching');
    return mvx_fetch_all_settings_for_searching($request);
}
function wcmp_bulk_todo_pending_product($request) {
    wc_deprecated_function( 'wcmp_bulk_todo_pending_product', '4.0.0', 'mvx_bulk_todo_pending_product');
    return mvx_bulk_todo_pending_product($request);
}
function wcmp_dismiss_and_approve_vendor_product_questions($request) {
    wc_deprecated_function( 'wcmp_dismiss_and_approve_vendor_product_questions', '4.0.0', 'mvx_dismiss_and_approve_vendor_product_questions');
    return mvx_dismiss_and_approve_vendor_product_questions($request);
}
function wcmp_dismiss_and_approve_vendor_coupon($request) {
    wc_deprecated_function( 'wcmp_dismiss_and_approve_vendor_coupon', '4.0.0', 'mvx_dismiss_and_approve_vendor_coupon');
    return mvx_dismiss_and_approve_vendor_coupon($request);
}
function wcmp_approve_vendor($request) {
    wc_deprecated_function( 'wcmp_approve_vendor', '4.0.0', 'mvx_approve_vendor');
    return mvx_approve_vendor($request);
}


function wcmp_reject_vendor($request) {
    wc_deprecated_function( 'wcmp_reject_vendor', '4.0.0', 'mvx_reject_vendor');
    return mvx_reject_vendor($request);
}
function wcmp_dismiss_vendor($request) {
    wc_deprecated_function( 'wcmp_dismiss_vendor', '4.0.0', 'mvx_dismiss_vendor');
    return mvx_bulk_todo_pending_product($request);
}

function wcmp_reject_vendor($request) {
    wc_deprecated_function( 'wcmp_reject_vendor', '4.0.0', 'mvx_reject_vendor');
    return mvx_reject_vendor($request);
}
function wcmp_dismiss_vendor($request) {
    wc_deprecated_function( 'wcmp_dismiss_vendor', '4.0.0', 'mvx_dismiss_vendor');
    return mvx_dismiss_vendor($request);
}
function wcmp_dismiss_requested_vendors_query($request) {
    wc_deprecated_function( 'wcmp_dismiss_requested_vendors_query', '4.0.0', 'mvx_dismiss_requested_vendors_query');
    return mvx_dismiss_requested_vendors_query($request);
}
function wcmp_approve_product($request) {
    wc_deprecated_function( 'wcmp_approve_product', '4.0.0', 'mvx_approve_product');
    return mvx_approve_product($request);
}
function wcmp_update_custom_post_status($request) {
    wc_deprecated_function( 'wcmp_update_custom_post_status', '4.0.0', 'mvx_delete_post_details');
    return mvx_delete_post_details($request);
}
function wcmp_search_announcement($request) {
    wc_deprecated_function( 'wcmp_search_announcement', '4.0.0', 'mvx_search_announcement');
    return mvx_search_announcement($request);
}
function wcmp_search_knowledgebase($request) {
    wc_deprecated_function( 'wcmp_search_knowledgebase', '4.0.0', 'mvx_search_knowledgebase');
    return mvx_search_knowledgebase($request);
}
function wcmp_list_of_store_review() {
    wc_deprecated_function( 'wcmp_list_of_store_review', '4.0.0', 'mvx_list_of_store_review');
    return mvx_list_of_store_review();
}
function wcmp_list_of_pending_vendor_product() {
    wc_deprecated_function( 'wcmp_list_of_pending_vendor_product', '4.0.0', 'mvx_list_of_pending_vendor_product');
    return mvx_list_of_pending_vendor_product();
}
function wcmp_list_of_pending_vendor() {
    wc_deprecated_function( 'wcmp_list_of_pending_vendor', '4.0.0', 'mvx_list_of_pending_vendor');
    return mvx_list_of_pending_vendor();
}
function wcmp_list_of_pending_vendor_coupon() {
    wc_deprecated_function( 'wcmp_list_of_pending_vendor_coupon', '4.0.0', 'mvx_list_of_pending_vendor_coupon');
    return mvx_list_of_pending_vendor_coupon();
}
function wcmp_list_of_pending_transaction() {
    wc_deprecated_function( 'wcmp_list_of_pending_transaction', '4.0.0', 'mvx_list_of_pending_transaction');
    return mvx_list_of_pending_transaction();
}
function wcmp_list_of_pending_question($request, $extra_status = '') {
    wc_deprecated_function( 'wcmp_list_of_pending_question', '4.0.0', 'mvx_list_of_pending_question');
    return mvx_list_of_pending_question($request, $extra_status = '');
}
function wcmp_create_knowladgebase($request) {
    wc_deprecated_function( 'wcmp_create_knowladgebase', '4.0.0', 'mvx_create_knowladgebase');
    return mvx_create_knowladgebase($request);
}
function wcmp_display_list_knowladgebase($request) {
    wc_deprecated_function( 'wcmp_display_list_knowladgebase', '4.0.0', 'mvx_display_list_knowladgebase');
    return mvx_display_list_knowladgebase($request);
}
function wcmp_update_knowladgebase_display($request) {
    wc_deprecated_function( 'wcmp_update_knowladgebase_display', '4.0.0', 'mvx_update_knowladgebase_display');
    return mvx_update_knowladgebase_display($request);
}
function wcmp_update_knowladgebase($request) {
    wc_deprecated_function( 'wcmp_update_knowladgebase', '4.0.0', 'mvx_update_knowladgebase');
    return mvx_update_knowladgebase($request);
}
function wcmp_update_announcement($request) {
    wc_deprecated_function( 'wcmp_update_announcement', '4.0.0', 'mvx_update_announcement');
    return mvx_update_announcement($request);
}
function wcmp_list_of_all_tab_based_settings_field($request) {
    wc_deprecated_function( 'wcmp_list_of_all_tab_based_settings_field', '4.0.0', 'mvx_list_of_all_tab_based_settings_field');
    return mvx_list_of_all_tab_based_settings_field($request);
}
function wcmp_create_announcement($request) {
    wc_deprecated_function( 'wcmp_create_announcement', '4.0.0', 'mvx_create_announcement');
    return mvx_create_announcement($request);
}
function wcmp_display_announcement($request) {
    wc_deprecated_function( 'wcmp_display_announcement', '4.0.0', 'mvx_display_announcement');
    return mvx_display_announcement($request);
}
function wcmp_fetch_report_overview_data() {
    wc_deprecated_function( 'wcmp_fetch_report_overview_data', '4.0.0', 'mvx_fetch_report_overview_data');
    return mvx_fetch_report_overview_data();
}
function wcmp_get_report_overview_data($request) {
    wc_deprecated_function( 'wcmp_get_report_overview_data', '4.0.0', 'mvx_get_report_overview_data');
    return mvx_get_report_overview_data($request);
}
function wcmp_report_data($request) {
    wc_deprecated_function( 'wcmp_report_data', '4.0.0', 'mvx_report_data');
    return mvx_report_data($request);
}
function wcmp_update_commission_status($request) {
    wc_deprecated_function( 'wcmp_update_commission_status', '4.0.0', 'mvx_update_commission_status');
    return mvx_update_commission_status($request);
}


function wcmp_get_commission_id_status($request) {
    wc_deprecated_function( 'wcmp_get_commission_id_status', '4.0.0', 'mvx_get_commission_id_status');
    return mvx_get_commission_id_status($request);
}
function wcmp_details_specific_commission($request) {
    wc_deprecated_function( 'wcmp_details_specific_commission', '4.0.0', 'mvx_details_specific_commission');
    return mvx_details_specific_commission($request);
}
function wcmp_commission_delete($request) {
    wc_deprecated_function( 'wcmp_commission_delete', '4.0.0', 'mvx_commission_delete');
    return mvx_commission_delete($request);
}
function wcmp_update_specific_vendor_shipping_option($request) {
    wc_deprecated_function( 'wcmp_update_specific_vendor_shipping_option', '4.0.0', 'mvx_update_specific_vendor_shipping_option');
    return mvx_update_specific_vendor_shipping_option($request);
}
function wcmp_export_csv_for_report_product_chart($request) {
    wc_deprecated_function( 'wcmp_export_csv_for_report_product_chart', '4.0.0', 'mvx_export_csv_for_report_product_chart');
    return mvx_export_csv_for_report_product_chart($request);
}
function wcmp_show_commission_status_list() {
    wc_deprecated_function( 'wcmp_show_commission_status_list', '4.0.0', 'mvx_show_commission_status_list');
    return mvx_show_commission_status_list();
}
function wcmp_search_specific_commission($request) {
    wc_deprecated_function( 'wcmp_search_specific_commission', '4.0.0', 'mvx_search_specific_commission');
    return mvx_search_specific_commission($request);
}
function wcmp_all_commission_details($request) {
    wc_deprecated_function( 'wcmp_all_commission_details', '4.0.0', 'mvx_all_commission_details');
    return mvx_all_commission_details($request);
}
<<<<<<< Updated upstream
function mvx_find_specific_commission($commission_ids = array(), $status = '', $vendor_name = '') {
    wc_deprecated_function( 'mvx_find_specific_commission', '4.0.0', 'mvx_find_specific_commission');
=======
function wcmp_find_specific_commission($commission_ids = array(), $status = '', $vendor_name = '') {
    wc_deprecated_function( 'wcmp_find_specific_commission', '4.0.0', 'mvx_find_specific_commission');
>>>>>>> Stashed changes
    return mvx_find_specific_commission($commission_ids = array(), $status = '', $vendor_name = '');
}
function wcmp_commission_list_search($request) {
    wc_deprecated_function( 'wcmp_commission_list_search', '4.0.0', 'mvx_commission_list_search');
    return mvx_commission_list_search($request);
}
function wcmp_update_post_code($request) {
    wc_deprecated_function( 'wcmp_update_post_code', '4.0.0', 'mvx_update_post_code');
    return mvx_update_post_code($request);
}
function wcmp_toggle_shipping_method($request) {
    wc_deprecated_function( 'wcmp_toggle_shipping_method', '4.0.0', 'mvx_toggle_shipping_method');
    return mvx_toggle_shipping_method($request);
}
function wcmp_delete_shipping_method($request) {
    wc_deprecated_function( 'wcmp_delete_shipping_method', '4.0.0', 'mvx_delete_shipping_method');
    return mvx_delete_shipping_method($request);
}
function wcmp_update_vendor_shipping_method($request) {
    wc_deprecated_function( 'wcmp_update_vendor_shipping_method', '4.0.0', 'mvx_update_vendor_shipping_method');
    return mvx_update_vendor_shipping_method($request);
}
function wcmp_add_shipping_option() {
    wc_deprecated_function( 'wcmp_add_shipping_option', '4.0.0', 'mvx_add_shipping_option');
    return mvx_add_shipping_option();
}



function mvx_add_vendor_shipping_method($request) {
    wc_deprecated_function( 'mvx_add_vendor_shipping_method', '4.0.0', 'mvx_add_vendor_shipping_method');
    return mvx_add_vendor_shipping_method($request);
}
function mvx_backend_add_shipping_method($zone_id, $method_id, $vendor_id) {
    wc_deprecated_function( 'mvx_backend_add_shipping_method', '4.0.0', 'mvx_backend_add_shipping_method');
    return mvx_backend_add_shipping_method($zone_id, $method_id, $vendor_id);
}
function wcmp_delete_shipping_method($request) {
    wc_deprecated_function( 'wcmp_delete_shipping_method', '4.0.0', 'mvx_delete_shipping_method');
    return mvx_delete_shipping_method($request);
}
function wcmp_update_vendor_shipping_method($request) {
    wc_deprecated_function( 'wcmp_update_vendor_shipping_method', '4.0.0', 'mvx_update_vendor_shipping_method');
    return mvx_update_vendor_shipping_method($request);
}
function wcmp_add_shipping_option() {
    wc_deprecated_function( 'wcmp_add_shipping_option', '4.0.0', 'mvx_add_shipping_option');
    return mvx_add_shipping_option();
}



function mvx_specific_vendor_shipping_zone($request) {
    wc_deprecated_function( 'mvx_specific_vendor_shipping_zone', '4.0.0', 'mvx_specific_vendor_shipping_zone');
    return mvx_specific_vendor_shipping_zone($request);
}
function mvx_specific_vendor_shipping($request) {
    wc_deprecated_function( 'mvx_specific_vendor_shipping', '4.0.0', 'mvx_specific_vendor_shipping');
    return mvx_specific_vendor_shipping($request);
}
function mvx_vendor_list_search() {
    wc_deprecated_function( 'mvx_vendor_list_search', '4.0.0', 'mvx_vendor_list_search');
    return mvx_vendor_list_search();
}
function mvx_product_list_option() {
    wc_deprecated_function( 'mvx_product_list_option', '4.0.0', 'mvx_product_list_option');
    return mvx_product_list_option();
}
function mvx_specific_search_vendor($request) {
    wc_deprecated_function( 'mvx_specific_search_vendor', '4.0.0', 'mvx_specific_search_vendor');
    return mvx_specific_search_vendor($request);
}


function mvx_all_vendor_followers($request) {
    wc_deprecated_function( 'mvx_all_vendor_followers', '4.0.0', 'mvx_all_vendor_followers');
    return mvx_all_vendor_followers($request);
}
function mvx_create_vendor($request) {
    wc_deprecated_function( 'mvx_create_vendor', '4.0.0', 'mvx_create_vendor');
    return mvx_create_vendor($request);
}
function mvx_update_vendor($request) {
    wc_deprecated_function( 'mvx_update_vendor', '4.0.0', 'mvx_update_vendor');
    return mvx_update_vendor($request);
}
function mvx_vendor_details($request) {
    wc_deprecated_function( 'mvx_vendor_details', '4.0.0', 'mvx_vendor_details');
    return mvx_vendor_details($request);
}
function mvx_all_vendor_details($request) {
    wc_deprecated_function( 'mvx_all_vendor_details', '4.0.0', 'mvx_all_vendor_details');
    return mvx_all_vendor_details($request);
}


function mvx_list_all_vendor($specific_id = array(), $role = '') {
    wc_deprecated_function( 'mvx_list_all_vendor', '4.0.0', 'mvx_list_all_vendor');
    return mvx_list_all_vendor($specific_id = array(), $role = '');
}
function mvx_front_registration($request) {
    wc_deprecated_function( 'mvx_front_registration', '4.0.0', 'mvx_front_registration');
    return mvx_front_registration($request);
}
function mvx_save_registration_forms($request) {
    wc_deprecated_function( 'mvx_save_registration_forms', '4.0.0', 'mvx_save_registration_forms');
    return mvx_save_registration_forms($request);
}
function mvx_get_registration_forms_data() {
    wc_deprecated_function( 'mvx_get_registration_forms_data', '4.0.0', 'mvx_get_registration_forms_data');
    return mvx_get_registration_forms_data();
}
function mvx_save_dashpages($req) {
    wc_deprecated_function( 'mvx_save_dashpages', '4.0.0', 'mvx_save_dashpages');
    return mvx_save_dashpages($req);
}