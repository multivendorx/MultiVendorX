<?php
/**
 * REST API Vendors controller
 *
 * Handles requests to the /vendors endpoint.
 *
 * @author 		MultiVendorX
 * @category API
 * @package MultiVendorX/API
 * @since    3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Vendors controller class.
 *
 * @package MultiVendorX/API
 */
class MVX_REST_API_Vendors_Controller extends WC_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mvx/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'vendors';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'dc_vendor';

	/**
	 * Register the routes for coupons.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'multivendorx' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context'         => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/batch', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'batch_items' ),
				'permission_callback' => array( $this, 'batch_items_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			'schema' => array( $this, 'get_public_batch_schema' ),
		) );
	}
	
	public function get_item_permissions_check( $request ) {
		return true;
		if ( ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'mvx_rest_cannot_access', __( 'Sorry, you cannot check list vendors.', 'multivendorx' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}
	
	public function create_item_permissions_check( $request ) {
		return true;
		if ( ! current_user_can( 'create_users' ) ) {
			return new WP_Error( 'mvx_rest_cannot_create', __( 'Sorry, you cannot create vendors.', 'multivendorx' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}
	
	public function update_item_permissions_check( $request ) {
		return true;
		if ( ! current_user_can( 'edit_users' ) ) {
			return new WP_Error( 'mvx_rest_cannot_update', __( 'Sorry, you cannot update vendors.', 'multivendorx' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}
	
	public function delete_item_permissions_check( $request ) {
		return true;
		if ( ! current_user_can( 'delete_users' ) ) {
			return new WP_Error( 'mvx_rest_cannot_delete', __( 'Sorry, you cannot delete vendors.', 'multivendorx' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}
	
	public function batch_items_permissions_check( $request ) {
		return true;
		if ( ! current_user_can( 'edit_users' ) ) {
			return new WP_Error( 'mvx_rest_cannot_do_batch', __( 'Sorry, you cannot process batch.', 'multivendorx' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}
	
	/**
	 * Get the Vendor's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema         = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'multivendorx' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				// Need to define the rest
			),
		);
		
		return $this->add_additional_fields_schema( $schema );
	}
	
	
	/**
     * Prepare list of vendors for response
     *
     * @param WP_REST_Request $request Request object. Under this the param are as follows
     * @param per_page vendors per page
     * @param page current page number
     * @param orderby orderby field as per WP_User_Query
     * @param order order field as per WP_User_Query
     * @param status vendor status like pending - default active vendors 
     *
     * @return WP_REST_Response $response Response data.
     */
	public function get_items( $request ) {
		$params = $request->get_params();
		
        $args = array(
            'number' => $params['per_page'],
            'offset' => ( $params['page'] - 1 ) * $params['per_page']
        );

        if ( ! empty( $params['orderby'] ) ) {
            $args['orderby'] = $params['orderby'];
        }

        if ( ! empty( $params['order'] ) ) {
            $args['order'] = $params['order'];
        }
        
        if ( ! empty( $params['status'] ) ) {
        	if($params['status'] == 'pending') $args['role'] = 'dc_pending_vendor';
        	else $args['role'] = $this->post_type;
        }

        $object = array();
        $response = array();

        $args = wp_parse_args($args, array('role' => 'dc_vendor', 'fields' => 'ids', 'orderby' => 'registered', 'order' => 'ASC'));
        $user_query = new WP_User_Query($args);
        if (!empty($user_query->results)) {
            foreach ( $user_query->results as $vendor_id) {
            	$vendor = get_mvx_vendor($vendor_id);
            	$is_block = get_user_meta($vendor->id, '_vendor_turn_off', true);
                if($is_block) continue;
				$vendor_data    = $this->prepare_item_for_response( $vendor, $request );
				$object[] = $this->prepare_response_for_collection( $vendor_data );
			}
	
			$per_page = (int) ( ! empty( $request['per_page'] ) ? $request['per_page'] : 10 );
			$page = (int) ( ! empty( $request['page'] ) ? $request['page'] : 1 );
			$total_count = $user_query->get_total();
			$max_pages = ceil( $total_count / $per_page );
			
			$response = rest_ensure_response( $object );
			
			$response->header( 'X-WP-Total', $total_count );
			$response->header( 'X-WP-TotalPages', (int) $max_pages );
	
			$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );
	
			if ( $page > 1 ) {
				$prev_page = $page - 1;
				if ( $prev_page > $max_pages ) {
					$prev_page = $max_pages;
				}
				$prev_link = add_query_arg( 'page', $prev_page, $base );
				$response->link_header( 'prev', $prev_link );
			}
			
			if ( $max_pages > $page ) {
				$next_page = $page + 1;
				$next_link = add_query_arg( 'page', $next_page, $base );
				$response->link_header( 'next', $next_link );
			}
        }
        /**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to object type being prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Data          $object   Object data.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "mvx_rest_prepare_{$this->post_type}_object", $response, $object, $request );
	}
	
    /**
     * Prepare a single vendor output for response
     *
     * @param object $method
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $method, $request, $additional_fields = [] ) {
    	$vendor_term_id = get_user_meta( $method->id, '_vendor_term_id', true );
    	$vendor_review_info = mvx_get_vendor_review_info($vendor_term_id);
    	$avg_rating = number_format(floatval($vendor_review_info['avg_rating']), 1);
    	$rating_count = $vendor_review_info['total_rating'];
    	$vendor = get_mvx_vendor($method->id);
    	$args = array(
            'author' => $method->id,
            'post_status' => 'any',
            
        );
        $mvx_vendor_followed_by_customer = get_user_meta( $method->id, 'mvx_vendor_followed_by_customer', true ) ? get_user_meta( $method->id, 'mvx_vendor_followed_by_customer', true ) : array();
    	$vendor_object = apply_filters("mvx_rest_prepare_vendor_object_args", array(
    		'id' => $method->id,
    		'avatar_id'	=>	get_avatar_url($method->id),
    		'products_count'	=>	count($vendor->get_products(array())),
    		'orders_count'		=>	count(mvx_get_orders($args)),
    		'followers_count'	=>	count($mvx_vendor_followed_by_customer),
    		'login' => $method->user_data->data->user_login,
    		'first_name' => get_user_meta($method->id, 'first_name', true),
    		'last_name' => get_user_meta($method->id, 'last_name', true),
    		'nice_name'  => $method->user_data->data->user_nicename,
    		'display_name'  => $method->user_data->data->display_name,
    		'email'  => $method->user_data->data->user_email,
    		'url'  => $method->user_data->data->user_url,
			'registered'  => $method->user_data->data->user_registered,
			'status'  => $method->user_data->data->user_status,
			'roles'  => $method->user_data->roles,
			'allcaps'  => $method->user_data->allcaps,
			'timezone_string'  => get_user_meta($method->id, 'timezone_string', true),
			'gmt_offset'  => get_user_meta($method->id, 'gmt_offset', true),
			'shop' => array(
				'url'  => $method->permalink,
				'title'  => $method->page_title,
				'slug'  => $method->page_slug,
				'description'  => $method->description,
				'image'  => $method->image,
				'banner'  => $method->banner,
			),
			'address' => array(
				'address_1'  => $method->address_1,
				'address_2'  => $method->address_2,
				'city'  => $method->city,
				'state'  => $method->state,
				'country'  => $method->country,
				'postcode'  => $method->postcode,
				'phone'  => $method->phone,
			),
			'social' => array(
				'facebook'  => $method->fb_profile,
				'twitter'  => $method->twitter_profile,
				'google_plus'  => $method->google_plus_profile,
				'linkdin'  => $method->linkdin_profile,
				'youtube'  => $method->youtube,
				'instagram'  => $method->instagram,
			),
			'payment' => array(
				'payment_mode'  => $method->payment_mode,
				'bank_account_type'  => $method->bank_account_type,
				'bank_name'  => $method->bank_name,
				'bank_account_number'  => $method->bank_account_number,
				'bank_address'  => $method->bank_address,
				'account_holder_name'  => $method->account_holder_name,
				'aba_routing_number'  => $method->aba_routing_number,
				'destination_currency'  => $method->destination_currency,
				'iban'  => $method->iban,
				'paypal_email'  => $method->paypal_email,
			),
			'message_to_buyers'  => $method->message_to_buyers,
			'rating_count' => $rating_count,
			'avg_rating' => $avg_rating,
		  
		), $method, $request );

        $vendor_object = array_merge( $vendor_object, $additional_fields );
        $response = rest_ensure_response( $vendor_object );
        $response->add_links( $this->prepare_links( $vendor_object, $request ) );
        
        return apply_filters( "mvx_rest_prepare_{$this->post_type}_method", $response, $method, $request );
    }
    
    /**
     * Prepare links for the request.
     *
     * @param WC_Data         $object  Object data.
     * @param WP_REST_Request $request Request object.
     *
     * @return array                   Links for the given post.
     */
    protected function prepare_links( $object, $request ) {
    	$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );
 
		$links = array(
			'self' => array(
				'href'   => rest_url( trailingslashit( $base ) . $object['id'] ),
			),
			'collection' => array(
				'href'   => rest_url( $base ),
			)
		);

        return $links;
    }
    
    /**
	 * Create a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 *
	 *
	 * @password - to pass the user password
	 * @notify_vendor - if set true, the vendor will be notified via email.
	 * @payment_mode - Predefined values only
	 * @bank_account_type - Predefined values only	
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_{$this->post_type}_exists", sprintf( __( 'Cannot create existing %s.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}
		
		if ( empty( $request['login'] ) ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_{$this->post_type}_login_empty", sprintf( __( '%s login required.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}
		
		if ( empty( $request['email'] ) ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_{$this->post_type}_email_empty", sprintf( __( '%s email required.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}

		$userdata = array(
			'user_login' => isset($request['login']) ? sanitize_user($request['login']) : '',
			'user_email' => isset($request['email']) ? sanitize_email($request['email']) : '',
			'user_url' => isset($request['url']) ? wc_clean($request['url']) : '',
			'user_pass' => isset($request['password']) ? wc_clean($request['password']) : '',
			'user_nicename' => isset($request['nice_name']) ? wc_clean($request['nice_name']) : '',
			'display_name' => isset($request['display_name']) ? wc_clean($request['display_name']) : '',
			'first_name' => isset($request['first_name']) ? wc_clean($request['first_name']) : '',
			'last_name' => isset($request['last_name']) ? wc_clean($request['last_name']) : '',
			'role' => $this->post_type
		);
		
                if( email_exists( $request['email'] ) || username_exists( $request['login'] ) ) {
                    $user = ( email_exists( $request['email'] ) && get_user_by( 'email',  $request['email'] ) ) ? get_user_by( 'email',  $request['email'] ) : get_user_by( 'login',  $request['login'] );
                    if( $user ) {
                        $user->set_role( $this->post_type );
                        $user_id = $user->ID;
                    }
                } else {
                    $user_id = wp_insert_user( $userdata ) ;
                }
		
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}
		
		if(isset($request['notify_vendor']) && $request['notify_vendor']) wp_new_user_notification($user_id, null, 'user');

		$this->update_additional_fields_for_vendor( $user_id, $request );
		
		/**
		 * Fires after a single object is created or updated via the REST API.
		 *
		 * @param User ID         $user_id  Vendor ID for whom the meta keys will be updated.
		 * @param WP_REST_Request $request   Request object.
		 * @param boolean         $creating  True when creating object, false when updating.
		 */
		do_action( "mvx_rest_insert_{$this->post_type}_object", $user_id, $request, true );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( get_mvx_vendor($user_id), $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $user_id ) ) );

		return $response;
	}
	
	/**
     * Update additional fileds for vendor.
     *
     * @param User ID         $user_id  Vendor ID for whom the meta keys will be updated.
     * @param WP_REST_Request $request Request object.
     *
     * @return none.
     */
    protected function update_additional_fields_for_vendor( $user_id, $request ) {
    	$vendor_meta_key_list = array(
			'timezone_string' => isset($request['timezone_string']) ? wc_clean( wp_unslash( $request['timezone_string'] ) ) : '',
			'gmt_offset' => isset($request['gmt_offset']) ? wc_clean( wp_unslash( $request['gmt_offset'] ) ) : '',
			'_vendor_description' => isset($request['shop']['description']) ? wc_clean( wp_unslash( $request['shop']['description'] ) ) : '',
			'_vendor_address_1' => isset($request['address']['address_1']) ? wc_clean( wp_unslash( $request['address']['address_1'] ) ) : '',
			'_vendor_address_2' => isset($request['address']['address_2']) ? wc_clean( wp_unslash( $request['address']['address_2'] ) ) : '',
			'_vendor_city' => isset($request['address']['city']) ? wc_clean( wp_unslash( $request['address']['city'] ) ) : '',
			'_vendor_state' => isset($request['address']['state']) ? wc_clean( wp_unslash( $request['address']['state'] ) ) : '',
			'_vendor_country' => isset($request['address']['country']) ? wc_clean( wp_unslash( $request['address']['country'] ) ) : '',
			'_vendor_postcode' => isset($request['address']['postcode']) ? wc_clean( wp_unslash( $request['address']['postcode'] ) ) : '',
			'_vendor_phone' => isset($request['address']['phone']) ? absint( $request['address']['phone'] ) : 0,
			'_vendor_fb_profile' => isset($request['social']['facebook']) ? esc_url( $request['social']['facebook'] ) : '',
			'_vendor_twitter_profile' => isset($request['social']['twitter']) ? esc_url( $request['social']['twitter'] ) : '',
			'_vendor_linkdin_profile' => isset($request['social']['linkdin']) ? esc_url( $request['social']['linkdin'] ) : '',
			'_vendor_youtube' => isset($request['social']['youtube']) ? esc_url( $request['social']['youtube'] ) : '',
			'_vendor_instagram' => isset($request['social']['instagram']) ? esc_url( $request['social']['instagram'] ) : '',
			'_vendor_payment_mode' => isset($request['payment']['payment_mode']) ? wc_clean( wp_unslash( $request['payment']['payment_mode'] ) ) : '',
			'_vendor_bank_account_type' => isset($request['payment']['bank_account_type']) ? wc_clean( wp_unslash( $request['payment']['bank_account_type'] ) ): '',
			'_vendor_bank_name' => isset($request['payment']['bank_name']) ? wc_clean( wp_unslash( $request['payment']['bank_name'] ) ): '',
			'_vendor_bank_address' => isset($request['payment']['bank_address']) ? wc_clean( wp_unslash( $request['payment']['bank_address'] ) ) : '',
			'_vendor_account_holder_name' => isset($request['payment']['account_holder_name']) ? wc_clean( wp_unslash( $request['payment']['account_holder_name'] ) ): '',
			'_vendor_bank_account_number' => isset($request['payment']['bank_account_number']) ? wc_clean( wp_unslash( $request['payment']['bank_account_number'] ) ) : '',
			'_vendor_aba_routing_number' => isset($request['payment']['aba_routing_number']) ? wc_clean( wp_unslash( $request['payment']['aba_routing_number'] ) ) : '',
			'_vendor_destination_currency' => isset($request['payment']['destination_currency']) ? wc_clean( wp_unslash( $request['payment']['destination_currency'] ) ) : '',
			'_vendor_iban' => isset($request['payment']['iban']) ? wc_clean( wp_unslash( $request['payment']['iban'] ) ): '',
			'_vendor_paypal_email' => isset($request['payment']['paypal_email']) ? wc_clean( wp_unslash( $request['payment']['paypal_email'] ) ) : '',
			'_vendor_message_to_buyers' => wc_clean( wp_unslash( $request['message_to_buyers'] ) )
		);
		foreach($vendor_meta_key_list as $key => $value) {
			if($value != '') update_user_meta($user_id, $key, $value);
		}
		// Check for cover/store images, upload it and set it.
		if ( isset( $request['images'] ) ) {
			$vendor = $this->set_vendor_images( $user_id, $request['images'] );
		}
    }

    /**
	 * Set vendor images.
	 *
	 * @throws WC_REST_Exception REST API exceptions.
	 * @param WC_vendor $vendor vendor instance.
	 * @param array      $images  Images data.
	 * @return WC_vendor
	 *  Request Example:
		{
			"login": "abc",
			"email": "abc@example.com",
			"images": [
				{
					"src" / "id": "http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg" / 12,
					"position": "cover"/"store"
				}
			]
		}
	*/

	protected function set_vendor_images( $vendor, $images ) {
		if ( is_array( $images ) ) {
			foreach ( $images as $image ) {
				$attachment_id = isset( $image['id'] ) ? absint( $image['id'] ) : 0;
				if ( 0 === $attachment_id && isset( $image['src'] ) ) {
					$upload = wc_rest_upload_image_from_url( esc_url_raw( $image['src'] ) );

					if ( is_wp_error( $upload ) ) {
						if ( ! apply_filters( 'mvx_rest_suppress_image_upload_error', false, $upload, $vendor, $images ) ) {
							throw new WC_REST_Exception( 'mvx_vendor_image_upload_error', $upload->get_error_message(), 400 );
						} else {
							continue;
						}
					}
					$attachment_id = wc_rest_set_uploaded_image_as_attachment( $upload, $vendor );
				}
				if ( ! wp_attachment_is_image( $attachment_id ) ) {
					throw new WC_REST_Exception( 'mvx_vendor_invalid_image_id', sprintf( __( '#%s is an invalid image ID.', 'multivendorx' ), $attachment_id ), 400 );
				}
				// For crop section start
				$cropped == false;
				if ( isset( $image['position'] ) && 'store' === $image['position'] ) {
					$cropped = wp_crop_image( $attachment_id, 0, 0, 1000,1000, 100, 100 );
				} elseif ( isset( $image['position'] ) && 'cover' === $image['position'] ) {
					$cropped = wp_crop_image( $attachment_id, 0, 0, 1920,622, 1200, 390 );
				} else {
					return new WP_Error( 'mvx_rest_wrong_position', __( 'Wrong position name.', 'multivendorx' ), array( 'status' => 404 ) );
				}
				if (!$cropped || is_wp_error($cropped)) {
					wp_send_json_error(array('message' => __('Image could not be processed. Please go back and try again.', 'multivendorx')));
				}
        		$cropped = apply_filters('mvx_rest_create_file_in_uploads', $cropped, $attachment_id); 
				$parent = get_post($attachment_id);
				$parent_url = $parent->guid;
				$url = str_replace(basename($parent_url), basename($cropped), $parent_url);

				$size = @getimagesize($cropped);
				$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';
				$object = array(
					'ID' => $attachment_id,
					'post_title' => basename($cropped),
					'post_content' => $url,
					'post_mime_type' => $image_type,
					'guid' => $url
					);

				// Its override actual image with cropped one
				if( !apply_filters( 'mvx_crop_image_override_with_original', false, $attachment_id, $_POST ) ) unset($object['ID']); 

				$attachment_id = wp_insert_attachment($object, $cropped);

				$metadata = wp_generate_attachment_metadata($attachment_id, $cropped);
				wp_update_attachment_metadata($attachment_id, $metadata);

				/************* crop section end **************************/

				if ( isset( $image['position'] ) && 'store' === $image['position'] ) {
					update_user_meta( $vendor, '_vendor_image', $attachment_id );
				} elseif ( isset( $image['position'] ) && 'cover' === $image['position'] ) {
					update_user_meta( $vendor, '_vendor_banner', $attachment_id );
				}
				// Set the image alt if present.
				if ( ! empty( $image['alt'] ) ) {
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', wc_clean( $image['alt'] ) );
				}

				// Set the image name if present.
				if ( ! empty( $image['name'] ) ) {
					wp_update_post( array( 'ID' => $attachment_id, 'post_title' => $image['name'] ) );
				}
			}
		} 
		return $vendor;
	}
    
    /**
     * Get details for a single vendor for response
     *
     * @param WP_REST_Request $request Request object. Under this the param are as follows
     *
     * @return WP_REST_Response $response Response data.
     */
	public function get_item( $request ) {
		if ( empty( $request['id'] ) || $request['id'] == '' ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_{$this->post_type}_insufficient_param", sprintf( __( 'Parameter insufficient for %s.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}
		
		if( ! is_user_mvx_vendor($request['id']) ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_is_not_a_{$this->post_type}", sprintf( __( 'User is not a %s.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}
		
		$vendor = get_mvx_vendor($request['id']);
		$vendor_data = $this->prepare_item_for_response( $vendor, $request );
 
		$response = rest_ensure_response( $vendor_data );
  
		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to object type being prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param MVX_Vendor      $vendor   Vendor object data.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "mvx_rest_prepare_single_{$this->post_type}_object", $response, $vendor, $request );
	}
	
	/**
	 * Update a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 *
	 */
	public function update_item( $request ) {
		if ( empty( $request['id'] ) || $request['id'] == '' ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_{$this->post_type}_insufficient_param", sprintf( __( 'Parameter insufficient for %s.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}
		
		if( ! is_user_mvx_vendor($request['id']) ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_is_not_a_{$this->post_type}", sprintf( __( 'User is not a %s.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}

		$userdata = array(
			'ID' => isset( $request['id'] ) ? absint($request['id']) : 0,
			'user_email' => isset( $request['email'] ) ? sanitize_email($request['email']) : '',
			'user_url' => isset( $request['url'] ) ? wc_clean($request['url']) : '',
			'user_pass' => isset( $request['password'] ) ? wc_clean($request['password']) : '',
			'user_nicename' => isset( $request['nice_name'] ) ? wc_clean($request['nice_name']) : '',
			'display_name' => isset( $request['display_name'] ) ? wc_clean($request['display_name']) : '',
			'first_name' => isset( $request['first_name'] ) ? wc_clean($request['first_name']) : '',
			'last_name' => isset( $request['last_name'] ) ? wc_clean($request['last_name']) : '',
			'role' => $this->post_type
		);

		
		$user_id = wp_update_user( $userdata ) ;
		
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$this->update_additional_fields_for_vendor( $user_id, $request );
		
		/**
		 * Fires after a single object is created or updated via the REST API.
		 *
		 * @param User ID         $user_id  Vendor ID for whom the meta keys will be updated.
		 * @param WP_REST_Request $request   Request object.
		 * @param boolean         $creating  True when creating object, false when updating.
		 */
		do_action( "mvx_rest_insert_{$this->post_type}_object", $user_id, $request, false );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( get_mvx_vendor($user_id), $request );
		$response = rest_ensure_response( $response );
		$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $user_id ) ) );

		return $response;
	}
	
	/**
	 * Delete a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 *
	 */
	public function delete_item( $request ) {
		require_once(ABSPATH.'wp-admin/includes/user.php');
		
		if ( empty( $request['id'] ) || $request['id'] == '' ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_{$this->post_type}_insufficient_param", sprintf( __( 'Parameter insufficient for %s.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}
		
		if( ! is_user_mvx_vendor($request['id']) ) {
			/* translators: %s: post type */
			return new WP_Error( "mvx_rest_is_not_a_{$this->post_type}", sprintf( __( 'User is not a %s.', 'multivendorx' ), $this->post_type ), array( 'status' => 400 ) );
		}

		$previous = $this->prepare_item_for_response( get_mvx_vendor( $request['id'] ), $request );
		
		$deleted = wp_delete_user( $request['id'] ) ;
		
		if ( ! $deleted ) {
			return new WP_Error( 'mvx_rest_cannot_delete', __( 'The vendor cannot be deleted.', 'multivendorx' ), array( 'status' => 500 ) );
        }
 
		$request->set_param( 'context', 'view' );
		$response = new WP_REST_Response();
		$response->set_data( array( 'deleted' => true, 'previous' => $previous->get_data() ) );
    
		/**
		 * Fires after a single object is deleted via the REST API.
		 *
		 * @param UserId          $user_id   User ID to delete.
		 * @param WP_REST_Request $request   Request object.
		 */
		do_action( "mvx_rest_delete_{$this->post_type}_object", $request['id'], $request );

		return $response;
	}
}
