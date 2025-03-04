import { __ } from '@wordpress/i18n';

export default {
    id: 'products_capability',
    priority: 6,
    name: __('Products Capability', 'multivendorx'),
    desc:  __("Manage product-related capabilities that you want sellers to have.", 'multivendorx'),
    icon: 'adminLib-wholesale',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: 'is_submit_product',
            type: 'checkbox',
            label: __( 'Submit Products', 'multivendorx' ),
            desc:  __('Enables sellers to add new products and submit them for admin approval', 'multivendorx'),
            options: [
                {
                    key: "is_submit_product",
                    value: "is_submit_product",
                }
            ],
            look:'toggle',
        },
        {
            key: 'is_published_product',
            type: 'checkbox',
            label: __( 'Publish Products', 'multivendorx' ),
            desc:  __('Lets sellers can publish products on the website without waiting for approval', 'multivendorx'),
            options: [
                {
                    key: "is_published_product",
                    value: "is_published_product",
                }
            ],
            look:'toggle',
        },
        {
            key: 'is_edit_delete_published_product',
            type: 'checkbox',
            label: __( 'Edit Published Products', 'multivendorx' ),
            desc:  __('Makes it possible for sellers to edit and delete a published product.', 'multivendorx'),
            options: [
                {
                    key: "is_edit_delete_published_product",
                    value: "is_edit_delete_published_product",
                }
            ],
            look:'toggle',
        },
        {
            key: 'publish_and_submit_products',
            type: 'checkbox',
            label: __( 'Publish and Submit Re-edited Products', 'multivendorx' ),
            desc:  __('Allows sellers to list their products while submitting them to your for revision', 'multivendorx'),
            options: [
                {
                    key: "publish_and_submit_products",
                    value: "publish_and_submit_products",
                }
            ],
            look:'toggle',
        },
        {
            key: 'is_submit_coupon',
            type: 'checkbox',
            label: __( 'Submit Coupons', 'multivendorx' ),
            desc:  __('Equips sellers with the ability to create their own coupons', 'multivendorx'),
            options: [
                {
                    key: "is_submit_coupon",
                    value: "is_submit_coupon",
                }
            ],
            look:'toggle',
        },
        {
            key: 'is_published_coupon',
            type: 'checkbox',
            label: __( 'Publish Coupons', 'multivendorx' ),
            desc:  __('Gives sellers the ability to publish coupons on your website', 'multivendorx'),
            options: [
                {
                    key: "is_published_coupon",
                    value: "is_published_coupon",
                }
            ],
            look:'toggle',
        },
        {
            key: 'is_edit_delete_published_coupon',
            type: 'checkbox',
            label: __( 'Edit Coupons', 'multivendorx' ),
            desc:  __('Sellers gain the option to edit, re-use or delete a published coupons', 'multivendorx'),
            options: [
                {
                    key: "is_edit_delete_published_coupon",
                    value: "is_edit_delete_published_coupon",
                }
            ],
            look:'toggle',
        },
        {
            key: 'is_upload_files',
            type: 'checkbox',
            label: __( 'Upload Media Files', 'multivendorx' ),
            desc:  __('Let Vendors upload media like ebooks, music, video, images etc', 'multivendorx'),
            options: [
                {
                    key: "is_upload_files",
                    value: "is_upload_files",
                }
            ],
            look:'toggle',
        },
        {
            key: "sku_generator_simple",
            type: "select",
            label: __( 'Generate Simple / Parent SKUs:', 'multivendorx' ),
            desc: __( 'Determine how SKUs for simple, external, or parent products will be generated.', 'multivendorx' ),
            options: [
                {
                    key: "choose_options",
                    label: __('Choose options', 'multivendorx'),
                    value: 'choose_options'
                },
                {
                    key: "never",
                    label: __('Never (let me set them)', 'multivendorx'),
                    value: 'never'
                },
                {
                    key: "slugs",
                    label: __('Using the product slug (name)', 'multivendorx'),
                    value: 'slugs'
                },
                {
                    key: "ids",
                    label: __('Using the product ID)', 'multivendorx'),
                    value: 'ids'
                },
            ],
        },
        {
            key: "sku_generator_variation",
            type: "select",
            label: __( 'Generate Variation SKUs:', 'multivendorx' ),
            desc: __( 'Determine how SKUs for product variations will be generated.', 'multivendorx' ),
            options: [
                {
                    key: "choose_options",
                    label: __('Choose options', 'multivendorx'),
                    value: 'choose_options'
                },
                {
                    key: "never",
                    label: __('Never (let me set them)', 'multivendorx'),
                    value: 'never'
                },
                {
                    key: "slugs",
                    label: __('Using the attribute slugs (names)', 'multivendorx'),
                    value: 'slugs'
                },
                {
                    key: "ids",
                    label: __('Using the variation ID)', 'multivendorx'),
                    value: 'ids'
                },
            ],
        },
        {
            key: "sku_generator_attribute_spaces",
            type: "select",
            label: __( 'Replace spaces in attributes?', 'multivendorx' ),
            desc: __( 'Replace spaces in attribute names when used in a SKU.', 'multivendorx' ),
            options: [
                {
                    key: "choose_options",
                    label: __('Choose options', 'multivendorx'),
                    value: 'choose_options'
                },
                {
                    key: "no",
                    label: __('Do not replace spaces in attribute names.', 'multivendorx'),
                    value: 'no'
                },
                {
                    key: "underscore",
                    label: __('Replace spaces with underscores', 'multivendorx'),
                    value: 'underscore'
                },
                {
                    key: "dash",
                    label: __('Replace spaces with dashes / hyphens', 'multivendorx'),
                    value: 'dash'
                },
                {
                    key: "none",
                    label: __('Remove spaces from attribute names', 'multivendorx'),
                    value: 'none'
                },
            ],
        },
        
    ]
}