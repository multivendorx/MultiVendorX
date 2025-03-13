import { __ } from '@wordpress/i18n';

export default {
    id: 'product_advertising',
    priority: 15,
    name: __('Product Advertising', 'mvx-pro'),
    desc: __('Control how vendors can advertise their products within your marketplace.', 'mvx-pro'),
    icon: 'adminLib-clock2',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "total_available_slot",
            type: 'number',
            label: __( 'Available advertisement slots', 'mvx-pro' ),
            desc:__( 'Define the number of advertising slots available to vendors. This determines how many products they can promote at any given time.', 'mvx-pro' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
        {
            key: "expire_after_days",
            type: 'number',
            label: __( 'Advertisement duration', 'mvx-pro' ),
            desc:__( 'Set the duration (in days) that a product will be advertised. Vendors can choose how long their products stay in the spotlight.', 'mvx-pro' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
        {
            key: "cost",
            type: 'number',
            label: sprintf('%1$s (%2$s)', __('Advertisement Cost', 'mvx-pro'), appLocalizer.woocommerce_currency),
            desc:__( 'Specify the cost for each advertisement slot. Enter "0" if you want to allow vendors to advertise for free.', 'mvx-pro' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
    ]
}