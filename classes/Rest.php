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
        register_rest_route( MultiVendorX()->rest_namespace, '/settings', [
            'methods'               => \WP_REST_Server::ALLMETHODS,
            'callback'              => [ $this, 'save_settings' ],
            'permission_callback'   => [ $this, 'multivenodrx_permission' ]
        ] );

        // // enable/disable the module
        register_rest_route( MultiVendorX()->rest_namespace, '/modules', [
            'methods'               => \WP_REST_Server::EDITABLE,
            'callback'              => [ $this, 'manage_module' ],
            'permission_callback'   => [ $this, 'multivenodrx_permission' ]
        ] );

        // fetch registration fileds data from settings page
        register_rest_route( MultiVendorX()->rest_namespace, '/get_registration', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'multivenodrx_get_registration_forms_data' ],
            'permission_callback' => [$this, 'multivenodrx_permission']
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
        $optionname         = 'multivendorx-' . $settingsname . '-settings';

        // save the settings in database
        MultiVendorX()->setting->update_option( $optionname, $get_settings_data );

        do_action( 'multivendorx_settings_after_save', $settingsname, $get_settings_data );

        $all_details[ 'error' ] = __( 'Settings Saved', 'multivendorx' );

        //setup wizard settings
        // $action = $request->get_param('action');

        // if ($action == 'enquiry') {
        //     $display_option = $request->get_param('displayOption');
        //     $restrict_user = $request->get_param('restrictUserEnquiry');
        //     MultiVendorX()->setting->update_setting('is_disable_popup', $display_option, 'catalog_all_settings_settings');
        //     MultiVendorX()->setting->update_setting('enquiry_user_permission', $restrict_user, 'catalog_all_settings_settings');
        // }
        
        // if ($action == 'quote') {
        //     $restrict_user = $request->get_param('restrictUserQuote');
        //     MultiVendorX()->setting->update_setting('quote_user_permission', $restrict_user, 'catalog_all_settings_settings');
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
        //     MultiVendorX()->modules->activate_modules([$module_id]);
        // }
        // // Handle the actions
        switch ( $action ) {
            case 'activate':
                MultiVendorX()->modules->activate_modules([$moduleId]);
                break;
            
            default:
                MultiVendorX()->modules->deactivate_modules([$moduleId]);
                break;
        }
    }

    public function multivenodrx_get_registration_forms_data() {
        $mvx_vendor_registration_form_data = MultiVendorX()->setting->get_option( 'multivendorx_new_vendor_registration_form_settings');
        return rest_ensure_response( $mvx_vendor_registration_form_data );
    }
    /**
     * Catalog rest api permission functions
     * @return bool
     */
	public function multivenodrx_permission() {
		// return current_user_can('manage_options');
        return true;
	}
}