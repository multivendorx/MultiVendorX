<?php
/**
 * MultiVendorX Frontend class file
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\MarketplaceRefund;

use MultiVendorX\Utill;
use MultiVendorX\Store\Store;
use MultiVendorX\FrontendScripts;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX Refund Frontend class
 *
 * @class       Frontend class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class Frontend {

	/**
	 * Frontend class constructor function.
	 */
	public function __construct() {

		add_action(
			'woocommerce_order_details_after_order_table',
			array( $this, 'multivendorx_refund_return_btn_customer_my_account' ),
			10
		);

		add_filter(
			'multivendorx_register_scripts',
			array( $this, 'register_script' )
		);

		add_filter(
			'multivendorx_localize_scripts',
			array( $this, 'localize_scripts' )
		);

		add_action(
			'wp_enqueue_scripts',
			array( $this, 'load_scripts' )
		);

		add_action(
			'wp',
			array( $this, 'multivendorx_handler_cust_request' )
		);

		add_action(
			'woocommerce_view_order',
			array( $this, 'view_order_content' )
		);

		add_filter(
			'multivendorx_approval_queue_count',
			array( $this, 'approval_count' ),
			10
		);
	}

	public function approval_count( $total ) {

		$query = wc_get_orders(
			array(
				'status'     => 'wc-refund-requested',
				'limit'      => 1,
				'paginate'   => true,
				'return'     => 'ids',
				'meta_query' => array(
					array(
						'key'     => 'multivendorx_store_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$refunds = ! empty( $query->total )
			? (int) $query->total
			: 0;

		$total += $refunds;

		return $total;
	}

	/**
     * Add refund and return button on customer my account order view page
     *
     * @param object $order Order object.
     */
	public function multivendorx_refund_return_btn_customer_my_account( $order ) {

		if ( ! is_wc_endpoint_url( 'view-order' ) || ! $order ) {
			return;
		}

		if ( ! MultiVendorX()->order->is_multivendorx_order( $order->get_id() ) ) {
			return;
		}

		$actions = $this->get_customer_request_actions( $order );

		foreach ( $actions as $action ) {

			$this->render_request_button( $order, $action );

			$this->render_request_popup( $order, $action );
		}
	}

	/**
	 * Get available customer request actions.
	 */
	private function get_customer_request_actions( $order ) {

		$actions = array();

		if ( $this->can_customer_request_refund( $order ) ) {
			$actions[] = 'refund';
		}

		if ( $this->can_customer_request_return( $order ) ) {
			$actions[] = 'return';
		}

		return $actions;
	}

	/**
	 * Check whether customer can request refund.
	 */
	private function can_customer_request_refund( $order ) {

		$allowed_statuses = (array) MultiVendorX()->setting->get_setting( 'customer_refund_status', array() );

		if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
			return false;
		}

		$refund_days = absint( MultiVendorX()->setting->get_setting( 'refund_days', 0 ) );

		if ($this->is_request_period_expired( $order, $refund_days ) ) {
			return false;
		}

		$refund_status = $order->get_meta( '_customer_refund_order', true );

		if ( in_array( $refund_status, array( 'refund_request', 'refund_accept','refund_reject', ), true ) ) {
			return false;
		}
        
        return true;
	}

	/**
	 * Check whether customer can request return.
	 */
	private function can_customer_request_return( $order ) {

		$allowed_statuses = (array) MultiVendorX()->setting->get_setting( 'customer_return_status', array() );

		if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
			return false;
		}

		$return_days = absint( MultiVendorX()->setting->get_setting( 'refund_days', 0 ) );

		if ( $this->is_request_period_expired( $order, $return_days ) ) {
			return false;
		}

		$return_status = $order->get_meta( '_customer_return_order', true );

		if ( in_array( $return_status, array( 'return_request', 'return_accept','return_reject', ), true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check whether request period has expired.
	 */
	private function is_request_period_expired( $order, $days ) {

		if ( $days <= 0 ) {
			return false;
		}

		$order_date = $order->get_date_created();

		if ( ! $order_date ) {
			return true;
		}

		$expiry_timestamp = strtotime(
			'+' . absint( $days ) . ' days',
			$order_date->getTimestamp()
		);

		return time() > $expiry_timestamp;
	}

	/**
	 * Render refund/return request button.
	 */
	private function render_request_button( $order, $action ) {

		$config = $this->get_request_button_config( $action, $order );

		if ( empty( $config ) ) {
			return;
		}
		?>

		<p class="form-row form-row-wide">

			<button
				type="button"
				class="button wp-element-button multivendorx-request-btn"
				id="<?php echo esc_attr( $config['id'] ); ?>"
			>
				<?php echo esc_html( $config['text'] ); ?>
			</button>

		</p>

		<?php
	}

	/**
	 * Get request button configuration.
	 */
	private function get_request_button_config( $action, $order ) {

		$configs = array(

			'refund' => array(
				'id'       => 'cust-request-refund-btn',
				'popup_id' => 'multivendorx-myac-order-refund-wrap',
                'popup_class'=> 'multivendorx-myac-order-refund-wrap',
				'text'     => apply_filters( 'multivendorx_my_account_refund_request_button_text', __( 'Request a refund', 'multivendorx' ), $order ),
			),

			'return' => array(
				'id'       => 'cust-request-return-btn',
				'popup_id' => 'multivendorx-myac-order-return-wrap',
                'popup_class'=> 'multivendorx-myac-order-return-wrap',
				'text'     => apply_filters( 'multivendorx_my_account_return_request_button_text', __( 'Request a return', 'multivendorx' ), $order ),
			),

		);

		return isset( $configs[ $action ] ) ? $configs[ $action ] : array();
	}

	/**
	 * Render refund/return popup.
	 */
	private function render_request_popup( $order, $action ) {

		$config = $this->get_request_popup_config( $action, $order );

		if ( empty( $config ) ) {
			return;
		}
		?>

		<div
			id="<?php echo esc_attr( $config['popup_id'] ); ?>"
			class="<?php echo esc_attr( $config['popup_class'] ); ?> multivendorx-popup"
			style="display:none;"
		>

			<div class="multivendorx-popup-content">

				<form
					method="post"
					enctype="multipart/form-data"
				>
                 
                     <span class="popup-close"><i class="dashicons dashicons-no-alt"></i></span>

					<?php
					wp_nonce_field(
						$config['nonce_action'],
						$config['nonce_name']
					);
					?>

					<?php
					$this->render_request_products( $order, $config );
					?>

					<?php
					$this->render_request_reasons( $config );
					?>

					<?php if ( ! empty( $config['additional_info'] ) ) : ?>

						<?php
						$this->render_additional_information( $config );
						?>

					<?php endif; ?>

					<?php
					$this->render_request_image_upload( $config );
					?>

					<p class="form-row form-row-wide woocommerce-form-row woocommerce-form-row--wide">

						<button
							type="submit"
							class="button wp-element-button"
							name="<?php echo esc_attr( $config['submit_name'] ); ?>"
							value="1"
						>
							<?php echo esc_html( $config['submit_text'] ); ?>
						</button>

					</p>

				</form>

			</div>

		</div>

		<?php
	}

	/**
	 * Get popup configuration.
	 */
	private function get_request_popup_config( $action, $order ) {

		$refund_reasons = (array) MultiVendorX()->setting->get_setting(
			'refund_reasons',
			array()
		);

		$return_settings = (array) MultiVendorX()->setting->get_setting(
			'return',
			array()
		);

		$configs = array(

			'refund' => array(
				'popup_id' => 'multivendorx-myac-order-refund-wrap',
				'popup_class' => 'multivendorx-myac-order-refund-wrap',
				'nonce_action' => 'customer_request_refund',
				'nonce_name' => 'cust-request-refund-nonce',
				'product_field' => 'refund_product',
				'product_class' => 'order-refund-product-list',
				'product_heading' => __(
					'Choose the product(s) you want a refund for',
					'multivendorx'
				),
                'reason_field' => 'refund_reason_option',
				'reason_other_field' => 'refund_reason_other',
				'reason_class' => 'refund_reason_option',
				'reason_label' => apply_filters(
					'multivendorx_my_account_refund_reason_label',
					__(
						'Please mention your reason for refund',
						'multivendorx' ), $order ),

				'reason_other_label' => __( 'Refund reason', 'multivendorx' ),
				'image_field' => 'product_img',
				'image_label' => __(
					'Upload an image of the product',
					'multivendorx'
				),
				'image_required' => false,
				'additional_info' => true,
				'additional_field' => 'refund_request_addi_info',
				'additional_label' => __(
					'Provide additional information',
					'multivendorx'
				),
				'submit_name' => 'cust_request_refund_sbmt',
				'submit_text' => __(
					'Submit refund request',
					'multivendorx'
				),
				'reasons' => $refund_reasons,
				'type' => 'refund',
			),

			'return' => array(
				'popup_id' => 'multivendorx-myac-order-return-wrap',
				'popup_class' => 'multivendorx-myac-order-return-wrap',
				'nonce_action' => 'customer_request_return',
				'nonce_name' => 'cust-request-return-nonce',
				'product_field' => 'return_product',
				'product_class' => 'order-return-product-list',
				'product_heading' => __(
					'Choose the product(s) you want to return',
					'multivendorx'
				),
				'reason_field' => 'return_reason_option',
				'reason_other_field' => 'return_reason_other',
				'reason_class' => 'return_reason_option',
				'reason_label' => apply_filters(
					'multivendorx_my_account_return_reason_label',
					__(
						'Please mention your reason for return',
						'multivendorx'
					), $order ),
				'reason_other_label' => __(
					'Return reason',
					'multivendorx'
				),
				'image_field' => 'return_product_img',
				'image_label' => __(
					'Upload product image',
					'multivendorx'
				),
				'image_required' => in_array( 'image_require', $return_settings, true ),
				'additional_info' => false,
				'additional_field' => '',
				'additional_label' => '',
				'submit_name' => 'cust_request_return_sbmt',
				'submit_text' => __(
					'Submit return request',
					'multivendorx'
				),
				'reasons' => $refund_reasons,
				'type' => 'return',
			),

		);

		return isset( $configs[ $action ] ) ? $configs[ $action ] : array();
	}

	/**
	 * Render products.
	 */
	private function render_request_products( $order, $config ) {
		?>

		<div class="form-row form-row-wide">

			<h3 class="section-heading">
				<?php echo esc_html( $config['product_heading'] ); ?>
			</h3>

			<div class="multivendorx-request-products">

				<?php foreach ( $order->get_items() as $item ) : ?>

					<?php
					$product = $item->get_product();

					if ( ! $product ) {
						continue;
					}
					?>

					<label
						class="<?php echo esc_attr( $config['product_class'] ); ?>"
					>

						<input
							type="checkbox"
							class="product-select-tag"
							name="<?php echo esc_attr( $config['product_field'] ); ?>[]"
							value="<?php echo esc_attr( $product->get_id() ); ?>"
						>

						<span class="product-image">
							<?php
							echo wp_kses_post(
								$product->get_image()
							);
							?>
						</span>

						<span class="product-name">
							<?php
							echo esc_html(
								$product->get_name()
							);
							?>
						</span>

					</label>

				<?php endforeach; ?>

			</div>

		</div>

		<?php
	}

	/**
	 * Render refund/return reasons.
	 */
	private function render_request_reasons( $config ) {
		?>

		<div class="form-row form-row-wide">

			<h3 class="section-heading">
				<?php echo esc_html( $config['reason_label'] ); ?>
			</h3>

			<div class="multivendorx-request-reasons">

				<?php foreach ( $config['reasons'] as $index => $reason ) : ?>

					<?php
					$reason_title = is_array( $reason )
						? ( $reason['title'] ?? '' )
						: $reason;

					if ( '' === $reason_title ) {
						continue;
					}

					$reason_value = 'refund' === $config['type']
						? $index
						: $reason_title;
					?>

					<label
						class="<?php echo esc_attr( $config['reason_class'] ); ?>"
						for="<?php echo esc_attr(
							$config['reason_field'] . '-' . $index
						); ?>"
					>

						<input
							type="radio"
							class="woocommerce-Input input-radio"
							name="<?php echo esc_attr( $config['reason_field'] ); ?>"
							id="<?php echo esc_attr(
								$config['reason_field'] . '-' . $index
							); ?>"
							value="<?php echo esc_attr( $reason_value ); ?>"
						>

						<?php echo esc_html( $reason_title ); ?>

					</label>

				<?php endforeach; ?>

				<label
					class="<?php echo esc_attr( $config['reason_class'] ); ?>"
					for="<?php echo esc_attr(
						$config['reason_field'] . '-other'
					); ?>"
				>

					<input
						type="radio"
						class="woocommerce-Input input-radio"
						name="<?php echo esc_attr( $config['reason_field'] ); ?>"
						id="<?php echo esc_attr(
							$config['reason_field'] . '-other'
						); ?>"
						value="others"
					>

					<?php esc_html_e(
						'Others reason',
						'multivendorx'
					); ?>

				</label>

			</div>

			<div class="form-row form-row-wide cust-rr-other">

				<label
					for="<?php echo esc_attr(
						$config['reason_other_field']
					); ?>"
				>
					<?php
					echo esc_html(
						$config['reason_other_label']
					);
					?>
				</label>

				<input
					type="text"
					class="woocommerce-Input input-text"
					name="<?php echo esc_attr(
						$config['reason_other_field']
					); ?>"
					id="<?php echo esc_attr(
						$config['reason_other_field']
					); ?>"
					autocomplete="off"
				>

			</div>

		</div>

		<?php
	}

	/**
	 * Render additional information.
	 */
	private function render_additional_information( $config ) {
		?>

		<div class="form-row form-row-wide">

			<label
				for="<?php echo esc_attr(
					$config['additional_field']
				); ?>"
			>
				<?php
				echo esc_html(
					$config['additional_label']
				);
				?>
			</label>

			<textarea
				class="woocommerce-Input input-text"
				name="<?php echo esc_attr(
					$config['additional_field']
				); ?>"
				id="<?php echo esc_attr(
					$config['additional_field']
				); ?>"
			></textarea>

		</div>

		<?php
	}

	/**
	 * Render image upload.
	 */
	private function render_request_image_upload( $config ) {
		?>

		<div class="form-row form-row-wide">

			<label
				for="<?php echo esc_attr(
					$config['image_field']
				); ?>"
			>

				<?php
				echo esc_html(
					$config['image_label']
				);
				?>

				<?php if ( ! empty( $config['image_required'] ) ) : ?>
					<span class="required">*</span>
				<?php endif; ?>

			</label>

			<input
				type="file"
				class="woocommerce-Input input-img"
				name="<?php echo esc_attr(
					$config['image_field']
				); ?>[]"
				id="<?php echo esc_attr(
					$config['image_field']
				); ?>"
				accept="image/jpeg,image/png,image/gif,image/webp"
				multiple
				<?php
				echo ! empty( $config['image_required'] )
					? 'required'
					: '';
				?>
			>

			<small>
				<?php esc_html_e(
					'You can select multiple images.',
					'multivendorx'
				); ?>
			</small>

		</div>

		<?php
	}

	/**
     * Get customer refund order messages
     *
     * @param object $order Order object.
     * @param array  $settings Settings array.
     */
    public function multivendorx_get_customer_refund_order_msg( $order, $settings = array() ) {
        if ( ! $order ) {
            return false;
        }
        $default_msg = apply_filters(
            'multivendorx_my_account_refund_order_messages',
            array(
				'order_status_not_allowed'      => __( 'Refund is not allowed for the current order status.', 'multivendorx' ),
				'order_refund_period_overed'    => __( 'Your refund period has expired', 'multivendorx' ),
				'order_refund_rejected'         => __( '*** Your request has been rejected ***', 'multivendorx' ),
				'order_refund_request_pending'  => __( 'Your request is pending.', 'multivendorx' ),
				'order_refund_request_accepted' => __( '*** Your request has been accepted. *** ', 'multivendorx' ),
            ),
            $order,
            $settings
        );

        $cust_refund_status = $order->get_meta( '_customer_refund_order', true ) ? $order->get_meta( '_customer_refund_order', true ) : '';
        $refund_days_limit  = MultiVendorX()->setting->get_setting( 'refund_days' ) ? absint( MultiVendorX()->setting->get_setting( 'refund_days' ) ) : apply_filters( 'multivendorx_refund_order_default_days_limit', 10, $order );
        $order_date         = $order->get_date_created()->format( 'Y-m-d' );
        $order_place_days   = time() - strtotime( $order_date );
        $message            = array();

        if ( abs( round( $order_place_days / 86400 ) ) > $refund_days_limit ) {
            $message['type'] = 'info';
            $message['msg']  = isset( $default_msg['order_refund_period_overed'] ) ? $default_msg['order_refund_period_overed'] : __( 'Your refund period has expired.', 'multivendorx' );
        }
        if ( ! in_array( $order->get_status(), MultiVendorX()->setting->get_setting( 'customer_refund_status', array() ), true ) ) {
            $message['type'] = 'info';
            $message['msg']  = isset( $default_msg['order_status_not_allowed'] ) ? $default_msg['order_status_not_allowed'] : __( 'Refund is not allowed for the current order status.', 'multivendorx' );
        }
        if ( 'refund_reject' === $cust_refund_status ) {
            $message['type'] = 'error';
            $message['msg']  = isset( $default_msg['order_refund_rejected'] ) ? $default_msg['order_refund_rejected'] : __( 'Sorry!! Your request has been rejected', 'multivendorx' );
        } elseif ( 'refund_request' === $cust_refund_status ) {
            $message['type'] = 'warning';
            $message['msg']  = isset( $default_msg['order_refund_request_pending'] ) ? $default_msg['order_refund_request_pending'] : __( 'Your request is pending.', 'multivendorx' );
        } elseif ( 'refund_accept' === $cust_refund_status ) {
            $message['type'] = 'success';
            $message['msg']  = isset( $default_msg['order_refund_request_accepted'] ) ? $default_msg['order_refund_request_accepted'] : __( 'Congratulation: *** Your request has been accepted. *** ', 'multivendorx' );
        }

        return $message;
    }

	/**
	 * Handle customer refund request.
	 *
	 * @return void
	 */
	public function multivendorx_handler_cust_requested_refund() {
        global $wp;

        // Sanitize POST data.
        $data = filter_input_array(
            INPUT_POST,
            array(
                'cust-request-refund-nonce' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'refund_product'            => array(
                    'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                    'flags'  => FILTER_REQUIRE_ARRAY,
                ),
                'refund_reason_option'      => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'refund_reason_other'       => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                'refund_request_addi_info'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            )
        );

        $nonce_value = $data['cust-request-refund-nonce'] ?? '';

        if ( ! wp_verify_nonce( $nonce_value, 'customer_request_refund' ) ) {
            return;
        }

        if ( empty( $data['refund_product'] ) ) {
            wc_add_notice( __( 'Kindly choose a product', 'multivendorx' ), 'error' );
            return;
        }

        if ( empty( $data['refund_reason_option'] ) ) {
            wc_add_notice( __( 'Kindly choose a refund reason', 'multivendorx' ), 'error' );
            return;
        }

        if ( ! isset( $wp->query_vars['view-order'] ) ) {
            return;
        }

        $order_id = absint( $wp->query_vars['view-order'] );
        $order    = wc_get_order( $order_id );

        // Clean request values.
        $reason_option            = wc_clean( $data['refund_reason_option'] ?? '' );
        $refund_reason_other      = wc_clean( $data['refund_reason_other'] ?? '' );
        $refund_request_addi_info = wc_clean( $data['refund_request_addi_info'] ?? '' );
        $refund_product           = array_map( 'wc_clean', (array) ( $data['refund_product'] ?? array() ) );

        // Build refund reason.
        $refund_reason_options = MultiVendorX()->setting->get_setting( 'refund_reasons', array() );
        $refund_reason         = ( 'others' === $reason_option )
            ? $refund_reason_other
            : ( $refund_reason_options[ $reason_option ]['title'] ?? '' );

        $uploaded_image_urls = array();
        $attach_ids          = array();

        /**
         * Handle uploaded images safely.
         *
         * PHPCS: The $_FILES superglobal cannot be sanitized using filter_input().
         * All indexes are validated, mime types checked, filenames sanitized.
         */
        /* phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
        $files = $_FILES['product_img'] ?? null;

        if ( ! empty( $files ) && ! empty( $files['name'] ) ) {
            // Normalize safely.
            $file_names  = array_map( 'sanitize_file_name', (array) ( $files['name'] ?? array() ) );
            $file_types  = (array) ( $files['type'] ?? array() );
            $file_tmp    = (array) ( $files['tmp_name'] ?? array() );
            $file_errors = (array) ( $files['error'] ?? array() );
            $file_sizes  = (array) ( $files['size'] ?? array() );

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $max_file_size = 10 * 1024 * 1024; // 10MB
            $allowed_mimes = array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'gif'          => 'image/gif',
                'png'          => 'image/png',
                'webp'         => 'image/webp',
            );

            foreach ( $file_names as $index => $name ) {
                if ( empty( $name ) ||
                    ! isset(
                        $file_errors[ $index ],
                        $file_sizes[ $index ],
                        $file_types[ $index ],
                        $file_tmp[ $index ]
                    )
                ) {
                    continue;
                }

                $sanitized_name = sanitize_file_name( $name );

                if ( UPLOAD_ERR_OK !== (int) $file_errors[ $index ] ) {
                    continue;
                }

                if ( (int) $file_sizes[ $index ] > $max_file_size ) {
                    continue;
                }

                $file_type = wp_check_filetype( $sanitized_name, $allowed_mimes );
                if ( empty( $file_type['type'] ) ) {
                    continue;
                }

                $file = array(
                    'name'     => $sanitized_name,
                    'type'     => sanitize_mime_type( $file_types[ $index ] ),
                    'tmp_name' => $file_tmp[ $index ],
                    'error'    => (int) $file_errors[ $index ],
                    'size'     => (int) $file_sizes[ $index ],
                );

                $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

                if ( $upload && ! isset( $upload['error'] ) ) {
                    $uploaded_image_urls[] = esc_url_raw( $upload['url'] );

                    $attachment = array(
                        'guid'           => $upload['url'],
                        'post_mime_type' => $upload['type'],
                        'post_title'     => sanitize_text_field( pathinfo( $sanitized_name, PATHINFO_FILENAME ) ),
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                    );

                    $attach_id = wp_insert_attachment( $attachment, $upload['file'] );

                    if ( $attach_id ) {
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                        wp_update_attachment_metadata( $attach_id, $attach_data );
                        $attach_ids[] = $attach_id;
                    }
                }
            }
        }
        /* phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */

        // Save order meta.
        $order->update_meta_data( Utill::ORDER_META_SETTINGS['customer_refund_order'], 'refund_request' );
        $order->update_meta_data( Utill::ORDER_META_SETTINGS['customer_refund_reason'], $refund_reason );
        $order->update_meta_data( Utill::ORDER_META_SETTINGS['customer_refund_product'], $refund_product );
        $order->update_meta_data( Utill::ORDER_META_SETTINGS['customer_refund_product_imgs'], $uploaded_image_urls );
        $order->update_meta_data( Utill::ORDER_META_SETTINGS['customer_refund_product_img_ids'], $attach_ids );
        $order->update_meta_data( Utill::ORDER_META_SETTINGS['customer_refund_addi_info'], $refund_request_addi_info );

        $order->set_status( 'refund-requested' );
        $order->save();

        $store_id = $order->get_meta( Utill::POST_META_SETTINGS['store_id'], true );
        if ( ! empty( $store_id ) ) {
            $store = new Store( $store_id );
            MultiVendorX()->notifications->send_notification_helper(
                'refund_requested',
                $store,
                $order,
                array(
                    'order_id' => $order->get_id(),
                    'category' => 'activity',
                )
            );
        }

        // Add order note with proper escaping.
        $user_info = get_userdata( MultiVendorX()->current_user_id );

        $comment_id = $order->add_order_note(
            sprintf(
                'Customer requested a refund for order %d.',
                (int) $order_id
            )
        );

        wp_update_comment(
            array(
                'comment_ID'           => $comment_id,
                'comment_author'       => sanitize_text_field( $user_info->user_login ),
                'comment_author_email' => sanitize_email( $user_info->user_email ),
            )
        );

        // Handle parent order.
        $parent_order_id = $order->get_parent_id();
        if ( $parent_order_id ) {
            $parent_order = wc_get_order( $parent_order_id );

            $comment_parent = $parent_order->add_order_note(
                sprintf(
                    'Customer requested a refund for child order %d.',
                    (int) $order_id
                )
            );

            wp_update_comment(
                array(
                    'comment_ID'           => $comment_parent,
                    'comment_author'       => sanitize_text_field( $user_info->user_login ),
                    'comment_author_email' => sanitize_email( $user_info->user_email ),
                )
            );
        }

        wc_add_notice( __( 'Refund request successfully submitted.', 'multivendorx' ) );
    }

	/**
	 * Handle customer return request.
	 *
	 * @return void
	 */
	public function multivendorx_handler_cust_requested_return() {

		global $wp;

		$data = filter_input_array(
			INPUT_POST,
			array(
				'cust_request_return_sbmt'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'cust-request-return-nonce' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'return_product' => array(
					'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
					'flags'  => FILTER_REQUIRE_ARRAY,
				),
				'return_reason_option'      => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'return_reason_other'       => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			)
		);

		$data = is_array( $data ) ? $data : array();

		if ( empty( $data['cust_request_return_sbmt'] ) ) {
			return;
		}

		$nonce = $data['cust-request-return-nonce'] ?? '';

		if ( ! wp_verify_nonce( $nonce, 'customer_request_return' ) ) {
			return;
		}

		if ( ! isset( $wp->query_vars['view-order'] ) ) {
			return;
		}

		$order_id = absint( $wp->query_vars['view-order'] );

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( (int) $order->get_customer_id() !== (int) get_current_user_id() ) {
			return;
		}

		$allowed_statuses = (array) MultiVendorX()->setting->get_setting(
			'customer_return_status',
			array()
		);

		if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {

			wc_add_notice(
				__(
					'Return is not allowed for this order status.',
					'multivendorx'
				),
				'error'
			);

			return;
		}

		$return_days = absint( MultiVendorX()->setting->get_setting( 'refund_days', 0 ) );

		if ( $return_days > 0 ) {

			$created = $order->get_date_created();

			if ( ! $created ) {
				return;
			}

			$expiry = strtotime(
				'+' . $return_days . ' days',
				$created->getTimestamp()
			);

			if ( time() > $expiry ) {

				wc_add_notice(
					__(
						'Your return period has expired.',
						'multivendorx'
					),
					'error'
				);

				return;
			}
		}

		$return_products = array_map( 'absint', (array) ($data['return_product'] ?? array() ) );

		if ( empty( $return_products ) ) {

			wc_add_notice(
				__(
					'Kindly choose a product.',
					'multivendorx'
				),
				'error'
			);

			return;
		}

		$return_reason = sanitize_textarea_field( $data['return_reason_option'] ?? '' );

		$additional_info = sanitize_textarea_field( $data['return_reason_other'] ?? '' );

		if ( 'others' === $return_reason && '' !== $additional_info ) {
			$return_reason = $additional_info;
		}

		if ( empty( $return_reason ) ) {

			wc_add_notice(
				__(
					'Kindly provide a return reason.',
					'multivendorx'
				),
				'error'
			);

			return;
		}

		$return_settings = (array) MultiVendorX()->setting->get_setting( 'return', array() );

		$image_required = in_array( 'image_require', $return_settings, true );

		$uploaded_urls  = array();
		$attachment_ids = array();

		if ( ! empty( $_FILES['return_product_img'] ) ) {

			$files = $_FILES['return_product_img'];

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$names = array_map( 'sanitize_file_name', (array) $files['name'] );

			$allowed_mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
				'webp'         => 'image/webp',
			);

			foreach ( $names as $index => $name ) {

				if ( empty( $name ) || empty( $files['tmp_name'][ $index ] ) ) {
					continue;
				}

				if ( UPLOAD_ERR_OK !== (int) $files['error'][ $index ] ) {
					continue;
				}

				if ( (int) $files['size'][ $index ] > 10 * 1024 * 1024 ) {
					continue;
				}

				$file_type = wp_check_filetype( $name, $allowed_mimes );

				if ( empty( $file_type['type'] ) ) {
					continue;
				}

				$file = array(
					'name'     => $name,
					'type'     => sanitize_mime_type(
						$files['type'][ $index ]
					),
					'tmp_name' => $files['tmp_name'][ $index ],
					'error'    => (int) $files['error'][ $index ],
					'size'     => (int) $files['size'][ $index ], );

				$upload = wp_handle_upload( $file,
					array(
						'test_form' => false,
						'mimes'     => $allowed_mimes,
					)
				);

				if ( ! $upload || isset( $upload['error'] ) ) {
					continue;
				}

				$uploaded_urls[] = esc_url_raw( $upload['url'] );

				$attachment = array(
					'guid'           => $upload['url'],
					'post_mime_type' => $upload['type'],
					'post_title'     => sanitize_text_field(
						pathinfo(
							$name,
							PATHINFO_FILENAME
						)
					),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);

				$attachment_id = wp_insert_attachment(
					$attachment,
					$upload['file']
				);

				if ( $attachment_id ) {

					$metadata =
						wp_generate_attachment_metadata(
							$attachment_id,
							$upload['file']
						);

					wp_update_attachment_metadata(
						$attachment_id,
						$metadata
					);

					$attachment_ids[] =
						$attachment_id;
				}
			}
        }

		if ( $image_required && empty( $uploaded_urls ) ) {
			wc_add_notice(
				__(
					'Please upload at least one product image.',
					'multivendorx'
				),
				'error'
			);

			return;
		}

		$order->update_meta_data( '_customer_return_order', 'return_request' );

		$order->update_meta_data( '_customer_return_product', $return_products );

		$order->update_meta_data( '_customer_return_reason', $return_reason );

		$order->update_meta_data( '_customer_return_additional_info', $additional_info );

		$order->update_meta_data( '_customer_return_product_imgs', $uploaded_urls );

		$order->update_meta_data( '_customer_return_product_img_ids', $attachment_ids );

		$order->set_status( 'return-requested' );

		$order->save();

		$store_id = $order->get_meta( Utill::POST_META_SETTINGS['store_id'], true );

		if ( ! empty( $store_id ) ) {

			$store = new Store( $store_id );

			MultiVendorX()->notifications->send_notification_helper(
				'return_requested',
				$store,
				$order,
				array(
					'order_id' => $order->get_id(),
					'category' => 'activity',
				)
			);
		}

		$comment_id = $order->add_order_note(
			sprintf(
				'Customer requested a return for order %d.',
				(int) $order_id
			)
		);

		$user_info = get_userdata(
			get_current_user_id()
		);

		if ( $user_info ) {

			wp_update_comment(
				array(
					'comment_ID' => $comment_id,

					'comment_author' =>
						sanitize_text_field(
							$user_info->user_login
						),

					'comment_author_email' =>
						sanitize_email(
							$user_info->user_email
						),
				)
			);
		}

		$parent_order_id = $order->get_parent_id();

		if ( $parent_order_id ) {

			$parent_order = wc_get_order(
				$parent_order_id
			);

			if ( $parent_order ) {

				$parent_order->add_order_note(
					sprintf(
						'Customer requested a return for child order %d.',
						(int) $order_id
					)
				);
			}
		}

		wc_add_notice(
			__(
				'Return request successfully submitted.',
				'multivendorx'
			)
		);
	}

	/**
	 * Handle customer refund and return actions.
	 */
	public function multivendorx_handler_cust_request() {

		$data = filter_input_array(
			INPUT_POST,
			array(
				'cust_request_refund_sbmt' =>
					FILTER_SANITIZE_FULL_SPECIAL_CHARS,

				'cust_request_return_sbmt' =>
					FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			)
		);

		$data = is_array( $data ) ? $data : array();

		if ( ! empty( $data['cust_request_refund_sbmt'] ) ) {

			$this->multivendorx_handler_cust_requested_refund();

			return;
		}

		if ( ! empty( $data['cust_request_return_sbmt'] ) ) {

			$this->multivendorx_handler_cust_requested_return();
		}
	}

	public function view_order_content( $order_id ) {
        if ( ! is_wc_endpoint_url( 'view-order' ) ) {
            return;
        }
        $order                    = wc_get_order( $order_id );
        $order_status             = $order->get_status();
        $refund_timeline_statuses = array(
            'refund-requested',
            'refund-accepted',
            'refund-rejected',
            'refunded',
        );

        if ( ! in_array( $order_status, $refund_timeline_statuses, true ) ) {
			return;
        }

        $refund_reason = $order->get_meta( 'multivendorx_customer_refund_reason' );
        $refund_note   = $order->get_meta( 'multivendorx_customer_refund_addi_info' );
        $refund_images = $order->get_meta( 'multivendorx_customer_refund_product_imgs' );

        $step1_class = '';
        $step2_class = '';
        $step3_class = '';

        $step1_icon = 'dashicons-minus';
        $step2_icon = 'dashicons-minus';
        $step3_icon = 'dashicons-minus';
        switch ( $order_status ) {
            case 'refund-requested':
                $step1_class = 'active';
                $step1_icon  = 'dashicons-ellipsis';
                break;

            case 'refund-accepted':
                $step1_class = 'completed';
                $step2_class = 'active';
                $step1_icon  = 'dashicons-yes';
                $step2_icon  = 'dashicons-ellipsis';
                break;

            case 'refunded':
                $step1_class = 'completed';
                $step2_class = 'completed';
                $step3_class = 'completed';
                $step1_icon  = 'dashicons-yes';
                $step2_icon  = 'dashicons-yes';
                $step3_icon  = 'dashicons-yes';
                break;

            case 'refund-rejected':
                $step1_class = 'completed';
                $step3_class = 'rejected';
                $step1_icon  = 'dashicons-yes';
                $step3_icon  = 'dashicons-no';
                break;
        }
        $store_reject_note = $order->get_meta( 'multivendorx_store_refund_reject_note' );
        $admin_reject_note = $order->get_meta( 'multivendorx_admin_refund_reject_note' );

        ?>
        <section class="woocommerce-customer-details multivendorx-refund-reason">
            <h2 class="woocommerce-column__title">
                <?php esc_html_e( 'Refund reason', 'multivendorx' ); ?>
            </h2>

            <address>
               
                <p class="category-name">
                    <strong><?php esc_html_e( 'Category:', 'multivendorx' ); ?></strong><br/>
                    <span class="multivendorx-badge">
                        <?php echo esc_html( $refund_reason ? $refund_reason : __( 'N/A', 'multivendorx' ) ); ?>
                    </span>
                </p>

                
                <address>
                    <?php
                    echo esc_html(
                        $refund_note
                            ? $refund_note
                            : __( 'No additional information provided.', 'multivendorx' )
                    );
                    ?>
                </address>
                
                <?php if ( ! empty( $refund_images ) ) : ?>
                    <p>
                        <strong><?php esc_html_e( 'Attached images:', 'multivendorx' ); ?></strong>
                    </p>
                    
                    <div class="refund-reason-image">
                        <?php foreach ( $refund_images as $img ) : ?>
                            <?php if ( ! empty( $img ) ) : ?>
                                <img src="<?php echo esc_url( $img ); ?>" alt="Refund image" />
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </address>

        </section>

        <section class="woocommerce-customer-details multivendorx-request-timeline">
            <h2 class="woocommerce-column__title">
                <?php esc_html_e( 'Request timeline', 'multivendorx' ); ?>
            </h2>
            <address>
                <div class="multivendorx-timeline">
                    <!-- Step 1 -->
                    <div class="multivendorx-timeline-item <?php echo esc_attr( $step1_class ); ?>">
                        <div class="timeline-icon">
                            <span class="dashicons <?php echo esc_attr( $step1_icon ); ?>"></span>
                        </div>
                        <div class="multivendorx-timeline-content">
                            <div class="multivendorx-timeline-title">
                                <strong><?php esc_html_e( 'Refund requested', 'multivendorx' ); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="multivendorx-timeline-item <?php echo esc_attr( $step2_class ); ?>">
                        <div class="timeline-icon">
                            <span class="dashicons <?php echo esc_attr( $step2_icon ); ?>"></span>
                        </div>
                        <div class="multivendorx-timeline-content">
                            <div class="multivendorx-timeline-title">
                                <strong><?php esc_html_e( 'Under review', 'multivendorx' ); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="multivendorx-timeline-item <?php echo esc_attr( $step3_class ); ?>">
                        <div class="timeline-icon">
                            <span class="dashicons <?php echo esc_attr( $step3_icon ); ?>"></span>
                        </div>
                        <div class="multivendorx-timeline-content">
                            <div class="multivendorx-timeline-title">
                                <strong>
                                    <?php
                                    if ( $order_status === 'refund-rejected' ) {
                                        esc_html_e( 'Refund rejected', 'multivendorx' );
                                    } else {
                                        esc_html_e( 'Decision pending', 'multivendorx' );
                                    }
                                    ?>
                                </strong>
                            </div>

                            <?php if ( $order_status === 'refund-rejected' ) : ?>
                                <div class="multivendorx-timeline-desc">
                                    <?php if ( $admin_reject_note ) : ?>
                                        <p>
                                            <strong><?php esc_html_e( 'Rejected by admin:', 'multivendorx' ); ?></strong>
                                            <?php echo esc_html( $admin_reject_note ); ?>
                                        </p>
                                    <?php elseif ( $store_reject_note ) : ?>
                                        <p>
                                            <strong><?php esc_html_e( 'Rejected by store:', 'multivendorx' ); ?></strong>
                                            <?php echo esc_html( $store_reject_note ); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ( $order_status === 'refunded' ) : ?>
                        <div class="multivendorx-timeline-item completed">
                            <div class="timeline-icon">
                                <span class="dashicons dashicons-yes"></span>
                            </div>
                            <div class="multivendorx-timeline-content">
                                <div class="multivendorx-timeline-title">
                                    <strong><?php esc_html_e( 'Refund processed', 'multivendorx' ); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </address>
        </section>
        <?php
    }

	/**
	 * Register MarketplaceRefund frontend script
	 *
	 * @param array $scripts Scripts array.
	 * @return array Modified scripts array
	 */
    public function register_script( $scripts ) {
        $scripts['multivendorx-MarketplaceRefund-frontend-script'] = array(
            'src'  => FrontendScripts::get_asset_path() . 'js/modules/MarketplaceRefund/' . MULTIVENDORX_PLUGIN_SLUG . '-frontend.min.js',
            'deps' => array( 'jquery','wp-i18n' ),
        );

        return $scripts;
    }

	/**
	 * Localize MarketplaceRefund frontend script.
	 *
	 * @param array $scripts Scripts array.
	 * @return array
	 */
	public function localize_scripts( $scripts ) {

		$scripts['multivendorx-MarketplaceRefund-frontend-script'] = array(
			'object_name' => 'MarketplaceRefundFrontend',
			'use_rest'    => true,
			'data'        => array(),
		);

		return $scripts;
	}

	/**
	 * Load MarketplaceRefund frontend scripts.
	 *
	 * @return void
	 */
	public function load_scripts() {

		if ( is_wc_endpoint_url( 'view-order' ) ) {

			FrontendScripts::load_scripts();

			FrontendScripts::enqueue_script(
				'multivendorx-MarketplaceRefund-frontend-script'
			);

			FrontendScripts::localize_scripts(
				'multivendorx-MarketplaceRefund-frontend-script'
			);
		}
	}
}