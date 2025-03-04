import { __ } from '@wordpress/i18n';

export default {
    id: 'orders',
    priority: 9,
    name:  __('Orders', 'multivendorx'),
    desc: __("Manage vendor's order releated capabilities", 'multivendorx'),
    icon: 'adminLib-order',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "disallow_vendor_order_status",
            label: __( 'Order Status Control', 'multivendorx' ),
            type: "checkbox",
            desc: __('Disallow sellers to change their order status', 'multivendorx'),
            options: [
                {
                    key: "disallow_vendor_order_status",
                    value: "disallow_vendor_order_status"
                }
            ],
            look:'toggle',
        },
        {
            key: "display_suborder_in_mail",
            label: __( 'Display Suborder in mail', 'multivendorx' ),
            type: "checkbox",
            desc: __('Display suborder number in mail.', 'multivendorx'),
            options: [
                {
                    key: "display_suborder_in_mail",
                    value: "display_suborder_in_mail"
                }
            ],
            look: 'toggle',
        }
    ]
};
