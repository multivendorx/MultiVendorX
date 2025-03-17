import { __ } from '@wordpress/i18n';

export default {
    id: 'review_management_tab',
    priority: 13,
    name: __('Reviews & Rating', 'multivendorx'),
    desc:  __('Manage settings for product and store review.', 'multivendorx'),
    icon: 'adminLib-settings',
    submitUrl: 'settings',
    modal: [
        {
            key:  'vendor_rating_page',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( "<b>Admin needs to enable product review from woocommerce settings</b>", 'multivendorx' ),
        },
        {
            key: 'is_sellerreview',
            type: 'checkbox',
            label: __( "Vendor Review", 'multivendorx' ),
            desc: __("Any customer can rate and review a vendor.", 'multivendorx'),
            options: [
                {
                    key: "is_sellerreview",
                    value: 'is_sellerreview'
                },
            ],
            look: 'toggle',
        },
        {
            key: 'is_sellerreview_varified',
            type: 'checkbox',
            label: __( "Buyer only reviews", 'multivendorx' ),
            desc: __("Allows you to accept reviews only from buyers purchasing the product.", 'multivendorx'),
            options: [
                {
                    key: "is_sellerreview_varified",
                    value: 'is_sellerreview_varified'
                },
            ],
            look: 'toggle',
        },
        {
            key: 'product_review_sync',
            type: 'checkbox',
            label: __( "Product Rating Sync", 'multivendorx' ),
            desc: __("Store Rating will be calculated based on Store Rating + Product Rating.", 'multivendorx'),
            options: [
                {
                    key: "product_review_sync",
                    value: 'product_review_sync'
                },
            ],
            look: 'toggle',
        },
        // Nested
    ]
}