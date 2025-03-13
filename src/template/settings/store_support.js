import { __ } from '@wordpress/i18n';

export default {
    id: 'store_support',
    priority: 17,
    name: __('Store Support', 'mvx-pro'),
    desc: __('Manage store support', 'mvx-pro'),
    icon: 'adminLib-support',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "display_in_order_details",
            label: __( 'Display support button on the Order Details', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Display store support button on the order details page.", 'mvx-pro'),
            options: [
                {
                    key: "display_in_order_details",
                    value: "display_in_order_details"
                }
            ],
            look: "toggle",
            moduleEnabled: 'store-support',
            proSetting: true,
        }
        
    ]
}