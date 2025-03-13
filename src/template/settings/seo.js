import { __ } from '@wordpress/i18n';

export default {
    id: 'seo',
    priority: 18,
    name: __('SEO', 'mvx-pro'),
    desc: __('Manage and Process vendor seo', 'mvx-pro'),
    icon: 'adminLib-support',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "is_yoast_allowed_for_vendors",
            label: __( "Enable SEO Support", 'mvx-pro' ),
            type: "checkbox",
            desc: __("Enable SEO Support for Vendors", 'mvx-pro'),
            options: [
                {
                    key: "is_yoast_allowed_for_vendors",
                    value: "is_yoast_allowed_for_vendors"
                }
            ],
            look: "toggle",
            moduleEnabled: 'store-seo',
            proSetting: true,
        },
        {
            key: "vendor_seo_options",
            type: "radio",
            label: __( 'SEO Mode', 'mvx-pro' ),
            options: [
                {
                    key: "yoast",
                    label: __('Yoast', 'mvx-pro'),
                    value: "yoast"
                },
                {
                    key: "rank_math",
                    label:  __('Rank Math', 'mvx-pro'),
                    value: "rank_math"
                }
            ],
            moduleEnabled: 'store-seo',
            proSetting: true,
        }
        
    ]
}