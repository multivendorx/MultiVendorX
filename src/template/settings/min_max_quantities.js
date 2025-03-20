import { __ } from '@wordpress/i18n';

export default {
    id: 'settings_min_max_tab',
    priority: 15,
    name: __('Min-Max Quantities', 'multivendorx'),
    desc: __("Manage Min-Max Quantities/Amount", 'multivendorx'),
    icon: 'adminLib-form-section',
    submitUrl: 'settings',
    modal: [
        {
            key: 'enable_min_max_quantity',
            type: 'checkbox',
            label: __( 'Enable Min/Max Quantities', 'multivendorx' ),
            desc: __('Activating this will set min and max quantities for selected products.', 'multivendorx'),
            options: [
                {
                    key: "enable_min_max_quantity",
                    value: "enable_min_max_quantity"
                }
            ],
            look: "toggle",
            moduleEnabled: 'min-max',
        },
        {
            key: 'enable_min_max_amount',
            type: 'checkbox',
            label: __( 'Enable Min/Max Amount', 'multivendorx' ),
            desc: __('Activating this will set min and max amount for selected products.', 'multivendorx'),
            options: [
                {
                    key: "enable_min_max_amount",
                    value: "enable_min_max_amount"
                }
            ],
            look: "toggle",
            moduleEnabled: 'min-max',
        },
    ]
}