import { __ } from '@wordpress/i18n';

export default {
    id: 'order_tab',
    priority: 11,
    name:  __('Orders', 'multivendorx'),
    desc: __("Control what actions vendors can take regarding their orders and how order details are displayed.", 'multivendorx'),
    icon: 'adminLib-order',
    submitUrl: 'settings',
    modal: [
        {
            key: "disallow_vendor_order_status",
            label: __( 'Order status control', 'multivendorx' ),
            type: "checkbox",
            desc: __('Decide whether vendors have the ability to change the status of their orders.', 'multivendorx'),
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
            label: __( 'Display suborder in mail', 'multivendorx' ),
            type: "checkbox",
            desc: __('Choose whether to include suborder numbers in order confirmation emails.', 'multivendorx'),
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
