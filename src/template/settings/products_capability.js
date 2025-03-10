import { __ } from '@wordpress/i18n';

export default {
    id: 'products_capability',
    priority: 6,
    name: __('Product permissions', 'multivendorx'),
    desc:  __("Control what sellers are allowed to do with their products in your marketplace.", 'multivendorx'),
    icon: 'adminLib-wholesale',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: 'is_submit_product',
            type: 'checkbox',
            label: __( 'Submit products for approval', 'multivendorx' ),
            desc:  __('Allow sellers to add new products, which will need admin approval before going live.', 'multivendorx'),
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
            desc:  __('Sellers can make changes to their products and either submit them for revision or publish them directly, depending on their permissions.', 'multivendorx'),
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
            label: __( 'Create coupons', 'multivendorx' ),
            desc:  __('Enable sellers to create their own discount coupons for their products.', 'multivendorx'),
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
            label: __( 'Publish coupons', 'multivendorx' ),
            desc:  __('Allow sellers to publish their coupons directly on the marketplace.', 'multivendorx'),
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
            label: __( 'Edit coupons', 'multivendorx' ),
            desc:  __('Grant sellers the ability to edit, reuse, or delete any coupons they have already published.', 'multivendorx'),
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
            label: __( 'Upload media files', 'multivendorx' ),
            desc:  __('Let sellers upload media files such as images, videos, eBooks, and music to enhance their product listings.', 'multivendorx'),
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