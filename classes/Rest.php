<?php

namespace MultiVendorX;

class Rest {
    /**
     * Rest class constructor function
     */
    public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_apis' ] );
    }

    /**
     * Register rest api
     * @return void
     */
    function register_rest_apis() {

        // save settings page data on database
        register_rest_route( MVX()->rest_namespace, '/settings', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'save_settings' ],
            'permission_callback'   => [ $this, 'multivenodrx_permission' ]
        ] );

        // // enable/disable the module
        register_rest_route( MVX()->rest_namespace, '/modules', [
            'methods'               => \WP_REST_Server::EDITABLE,
            'callback'              => [ $this, 'manage_module' ],
            'permission_callback'   => [ $this, 'multivenodrx_permission' ]
        ] );

	}

    // /**
    //  * Save global settings
    //  * @param mixed $request
    //  * @return \WP_Error|\WP_REST_Response
    //  */
    public function save_settings( $request ) {
        $all_details        = [];
        $get_settings_data  = $request->get_param( 'setting' );
        $settingsname       = $request->get_param( 'settingName' );
        $settingsname       = str_replace( "-", "_", $settingsname );
        $optionname         = 'mvx_' . $settingsname . '_tab_settings';

        error_log("Settings data : ".print_r($get_settings_data,true));
        error_log("Settings name : ".print_r($settingsname ,true));
        error_log("Settings option name : ".print_r($optionname,true));
        error_log("All Details : ".print_r($all_details,true));

        // save the settings in database
        MVX()->setting->update_option( $optionname, $get_settings_data );

        do_action( 'multivendorx_settings_after_save', $settingsname, $get_settings_data );

        $all_details[ 'error' ] = __( 'Settings Saved', 'MVX' );

        //setup wizard settings
        // $action = $request->get_param('action');

        // if ($action == 'enquiry') {
        //     $display_option = $request->get_param('displayOption');
        //     $restrict_user = $request->get_param('restrictUserEnquiry');
        //     MVX()->setting->update_setting('is_disable_popup', $display_option, 'catalog_all_settings_settings');
        //     MVX()->setting->update_setting('enquiry_user_permission', $restrict_user, 'catalog_all_settings_settings');
        // }
        
        // if ($action == 'quote') {
        //     $restrict_user = $request->get_param('restrictUserQuote');
        //     MVX()->setting->update_setting('quote_user_permission', $restrict_user, 'catalog_all_settings_settings');
        // }

        return rest_ensure_response($all_details);
	}

    // /**
    //  * Manage module setting. Active or Deactive modules.
    //  * @param mixed $request
    //  * @return void
    //  */
    public function manage_module( $request ) {
        $moduleId   = $request->get_param( 'id' );
        $action     = $request->get_param( 'action' );

        // Setup wizard module
        $modules = $request->get_param('modules');
        // foreach ($modules as $module_id) {
        //     MVX()->modules->activate_modules([$module_id]);
        // }
        // // Handle the actions
        switch ( $action ) {
            case 'activate':
                MVX()->modules->activate_modules([$moduleId]);
                break;
            
            default:
                MVX()->modules->deactivate_modules([$moduleId]);
                break;
        }
    }

    // /**
    //  * Manage module from setup wizard.
    //  * @param mixed $request
    //  * @return void
    //  */
    // // public function save_module( $request ) {
    // //     $modules = $request->get_param('modules');
    // //     foreach ($modules as $module_id) {
    // //         MVX()->modules->activate_modules([$module_id]);
    // //     }
    // // }

    // /**
    //  * Manage settings from setup wizard.
    //  * @param mixed $request
    //  * @return void
    //  */
    // // public function save_settings_wizard( $request ) {
    // //     $action = $request->get_param('action');

    // //     if ($action == 'enquiry') {
    // //         $display_option = $request->get_param('displayOption');
    // //         $restrict_user = $request->get_param('restrictUserEnquiry');
    // //         MVX()->setting->update_setting('is_disable_popup', $display_option, 'catalog_all_settings_settings');
    // //         MVX()->setting->update_setting('enquiry_user_permission', $restrict_user, 'catalog_all_settings_settings');
    // //     }
        
    // //     if ($action == 'quote') {
    // //         $restrict_user = $request->get_param('restrictUserQuote');
    // //         MVX()->setting->update_setting('quote_user_permission', $restrict_user, 'catalog_all_settings_settings');
    // //     }
    // // }

    /**
     * Catalog rest api permission functions
     * @return bool
     */
	public function multivenodrx_permission() {
		// return current_user_can('manage_options');
        return true;
	}
}