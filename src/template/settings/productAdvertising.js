import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-advertising',
    priority: 18,
    name: __( 'Product Advertising', 'multivendorx' ),
    desc: __( 'Control how vendors can advertise their products within your marketplace.', 'multivendorx' ),
    icon: 'adminLib-clock2',
    submitUrl: 'settings',
    modal: [
        {
            key: 'total_available_slot',
            type: 'number',
            label: __( 'Available advertisement slots', 'multivendorx' ),
            desc:__( 'Define the number of advertising slots available to vendors. This determines how many products they can promote at any given time.', 'multivendorx' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
        {
            key: 'expire_after_days',
            type: 'number',
            label: __( 'Advertisement duration', 'multivendorx' ),
            desc:__( 'Set the duration (in days ) that a product will be advertised. Vendors can choose how long their products stay in the spotlight.', 'multivendorx' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
        {
            key: 'cost',
            type: 'number',
            label: sprintf('%1$s (%2$s )', __( 'Advertisement Cost', 'multivendorx' ), appLocalizer.woocommerce_currency ),
            desc:__( 'Specify the cost for each advertisement slot. Enter \'0\' if you want to allow vendors to advertise for free.', 'multivendorx' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
    ]
}