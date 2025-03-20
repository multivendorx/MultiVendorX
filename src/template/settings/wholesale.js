import { __ } from '@wordpress/i18n';

export default {
    id: 'settings_wholesale_tab',
    priority: 19,
    name: __('Wholesale', 'mvx-pro'),
    desc: __('Wholesale', 'mvx-pro'),
    icon: 'adminLib-wholesale',
    submitUrl: 'settings',
    modal: [
        {
            key: "wholesale_price_display",
            type: "radio",
            label: __("Who can see wholesale price", "mvx-pro"),
            desc: __("Who can actually see the wholesale price in product page", "mvx-pro"),
            options: [
                {
                    key: "all_user",
                    label: __("Display wholesale price to all users", "mvx-pro"),
                    value: "all_user"
                },
                {
                    key: "wholesale_customer",
                    label: __("Display wholesale price to Wholesale customer only", "mvx-pro"),
                    value: "wholesale_customer"
                }
            ],
            moduleEnabled: 'wholesale',
            proSetting: true,
        },
        {
            key: "display_price_in_shop_archive",
            label: __( 'Show wholesale price on shop archive', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Show wholesale price in the shop archive.", 'mvx-pro'),
            options: [
                {
                    key: "display_price_in_shop_archive",
                    value: "display_price_in_shop_archive"
                }
            ],
            look: "toggle",
            moduleEnabled: 'wholesale',
            proSetting: true,
        },
        {
            key: 'need_approval_for_wholesale_customer',
            type: 'settingToggle',
            label: __( 'Need approval for customer', 'mvx-pro' ),
            desc: __( 'Customer need admin approval for becoming a wholesale customer.', 'mvx-pro' ),
            options: [
                {
                    key: 'yes',
                    label:__( 'Yes', 'mvx-pro' ),
                    value: 'yes'
                },
                {
                    key: 'no',
                    label: __( 'No', 'mvx-pro' ),
                    value: 'no'
                }
            ],
            moduleEnabled: 'wholesale',
            proSetting: true,
        },
    ]
}