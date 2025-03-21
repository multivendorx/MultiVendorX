import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-wholesale',
    priority: 19,
    name: __( 'Wholesale', 'multivendorx' ),
    desc: __( 'Wholesale', 'multivendorx' ),
    icon: 'adminLib-wholesale',
    submitUrl: 'settings',
    modal: [
        {
            key: 'wholesale_price_display',
            type: 'radio',
            label: __( 'Who can see wholesale price', 'multivendorx' ),
            desc: __( 'Who can actually see the wholesale price in product page', 'multivendorx' ),
            options: [
                {
                    key: 'all_user',
                    label: __( 'Display wholesale price to all users', 'multivendorx' ),
                    value: 'all_user'
                },
                {
                    key: 'wholesale_customer',
                    label: __( 'Display wholesale price to Wholesale customer only', 'multivendorx' ),
                    value: 'wholesale_customer'
                }
            ],
            moduleEnabled: 'wholesale',
            proSetting: true,
        },
        {
            key: 'display_price_in_shop_archive',
            label: __( 'Show wholesale price on shop archive', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Show wholesale price in the shop archive.', 'multivendorx' ),
            options: [
                {
                    key: 'display_price_in_shop_archive',
                    value: 'display_price_in_shop_archive'
                }
            ],
            look: 'toggle',
            moduleEnabled: 'wholesale',
            proSetting: true,
        },
        {
            key: 'need_approval_for_wholesale_customer',
            type: 'settingToggle',
            label: __( 'Need approval for customer', 'multivendorx' ),
            desc: __( 'Customer need admin approval for becoming a wholesale customer.', 'multivendorx' ),
            options: [
                {
                    key: 'yes',
                    label:__( 'Yes', 'multivendorx' ),
                    value: 'yes'
                },
                {
                    key: 'no',
                    label: __( 'No', 'multivendorx' ),
                    value: 'no'
                }
            ],
            moduleEnabled: 'wholesale',
            proSetting: true,
        },
    ]
}