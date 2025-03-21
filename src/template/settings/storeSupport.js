import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-store-support',
    priority: 20,
    name: __( 'Store Support', 'multivendorx' ),
    desc: __( 'Manage store support', 'multivendorx' ),
    icon: 'adminLib-support',
    submitUrl: 'settings',
    modal: [
        {
            key: 'display_in_order_details',
            label: __( 'Display support button on the Order Details', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Display store support button on the order details page.', 'multivendorx' ),
            options: [
                {
                    key: 'display_in_order_details',
                    value: 'display_in_order_details'
                }
            ],
            look: 'toggle',
            moduleEnabled: 'store-support',
            proSetting: true,
        }
        
    ]
}