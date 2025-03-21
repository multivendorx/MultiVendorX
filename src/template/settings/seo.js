import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-seo',
    priority: 21,
    name: __( 'SEO', 'multivendorx' ),
    desc: __( 'Manage and Process vendor seo', 'multivendorx' ),
    icon: 'adminLib-support',
    submitUrl: 'settings',
    modal: [
        {
            key: 'is_yoast_allowed_for_vendors',
            label: __( 'Enable SEO Support', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Enable SEO Support for Vendors', 'multivendorx' ),
            options: [
                {
                    key: 'is_yoast_allowed_for_vendors',
                    value: 'is_yoast_allowed_for_vendors'
                }
            ],
            look: 'toggle',
            moduleEnabled: 'store-seo',
            proSetting: true,
        },
        {
            key: 'vendor_seo_options',
            type: 'radio',
            label: __( 'SEO Mode', 'multivendorx' ),
            options: [
                {
                    key: 'yoast',
                    label: __( 'Yoast', 'multivendorx' ),
                    value: 'yoast'
                },
                {
                    key: 'rank_math',
                    label:  __( 'Rank Math', 'multivendorx' ),
                    value: 'rank_math'
                }
            ],
            moduleEnabled: 'store-seo',
            proSetting: true,
        }
        
    ]
}