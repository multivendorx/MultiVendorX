import { __ } from '@wordpress/i18n';

export default {
    id: 'spmv_pages',
    priority: 7,
    name: __('SPMV(Single Product Multiple Vendor)', 'multivendorx'),
    desc: __("Give sellers the option to add other seller's products into their store inventory.", 'multivendorx'),
    icon: 'adminLib-form-section',
    submitUrl: 'save-settings',
    modal: [
        {
            key: 'is_singleproductmultiseller',
            type: 'checkbox',
            label: __( 'Allow Vendor to Copy Products', 'multivendorx' ),
            desc: __('Let vendors search for products sold on your site and sell them from their store.', 'multivendorx'),
            options: [
                {
                    key: "is_singleproductmultiseller",
                    value: "is_singleproductmultiseller"
                }
            ],
            proSetting: true,
            look: "toggle",
            moduleEnabled: 'spmv',
        },
        {
            key: 'singleproductmultiseller_show_order',
            type: 'select',
            label:__( 'Display Shop Page Product', 'multivendorx' ),
            desc:__('Select the criteria on which the SPMV product is going to be based on.', 'multivendorx'),
            options: [
                {
                    key:"min-price",
                    label:__('Min Price', 'multivendorx'),
                    value:__('min-price', 'multivendorx'),
                },
                {
                    key:"max-price",
                    label:__('Max Price', 'multivendorx'),
                    value:__('max-price', 'multivendorx'),
                },
                {
                    key:"top-rated-vendor",
                    label:__('Top rated vendor', 'multivendorx'),
                    value:__('top-rated-vendor', 'multivendorx'),
                }
            ],
            proSetting: true,
            moduleEnabled: 'spmv',
        },
    ]
}