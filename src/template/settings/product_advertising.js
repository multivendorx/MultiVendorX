import { __ } from '@wordpress/i18n';

export default {
    id: 'product_advertising',
    priority: 13,
    name: __('Product Advertising', 'mvx-pro'),
    desc: __('Manage Product Advertising', 'mvx-pro'),
    icon: 'adminLib-clock2',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "total_available_slot",
            type: 'number',
            label: __( 'No. of Available Slot', 'mvx-pro' ),
            desc:__( 'Enter how many products can be advertised.', 'mvx-pro' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
        {
            key: "expire_after_days",
            type: 'number',
            label: __( 'Advertisement Duration', 'mvx-pro' ),
            desc:__( 'Enter the number of days the product will be advertised.', 'mvx-pro' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
        {
            key: "cost",
            type: 'number',
            label: sprintf('%1$s (%2$s)', __('Advertisement Cost', 'mvx-pro'), appLocalizer.woocommerce_currency),
            desc:__( 'Cost of per advertisement. Set 0 (zero) to purchase at no cost.', 'mvx-pro' ),
            moduleEnabled: 'advertisement',
            proSetting: true,
        },
    ]
}