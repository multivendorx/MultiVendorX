import { __ } from '@wordpress/i18n';

export default {
    id: 'products-capability',
    priority: 6,
    name: __( 'Product permissions', 'multivendorx' ),
    desc:  __( "Manage what sellers are allowed to do with their products in your marketplace.", 'multivendorx' ),
    icon: 'adminLib-wholesale',
    submitUrl: 'settings',
    modal: [
        {
            key: 'separator_content',
            type: 'section',
            desc: __( "Product Permission Options", "multivendorx" ),
            hint: __( "Control how sellers can submit and manage their products", "multivendorx" ),
        },
        {
            key: 'is_submit_product',
            type: 'checkbox',
            label: __( 'Submit Products for Approval', 'multivendorx' ),
            desc:  __( 'Enable this option to allow sellers to add new products, which will need admin approval before going live.', 'multivendorx' ),
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
            desc:  __( 'Allow sellers to directly publish their products on the marketplace without waiting for admin approval.', 'multivendorx' ),
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
            desc:  __( 'Give sellers the ability to edit or delete products that have already been published on the marketplace.', 'multivendorx' ),
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
            label: __( 'Publish & Submit Edited Products', 'multivendorx' ),
            desc:  __( 'Sellers can make changes to their products and either submit them for revision or publish them directly, depending on their permissions.', 'multivendorx' ),
            options: [
                {
                    key: "publish_and_submit_products",
                    value: "publish_and_submit_products",
                }
            ],
            look:'toggle',
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: __( "Coupon Management", "multivendorx" ),
            hint: __( "Give sellers control over their discount coupons.", "multivendorx" ),
        },
        {
            key: 'is_submit_coupon',
            type: 'checkbox',
            label: __( 'Create Coupons', 'multivendorx' ),
            desc:  __( 'Enable sellers to create their own discount coupons for their products.', 'multivendorx' ),
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
            desc:  __( 'Allow sellers to publish their coupons directly on the marketplace.', 'multivendorx' ),
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
            desc:  __( 'Grant sellers the ability to edit, reuse, or delete any coupons they have already published.', 'multivendorx' ),
            options: [
                {
                    key: "is_edit_delete_published_coupon",
                    value: "is_edit_delete_published_coupon",
                }
            ],
            look:'toggle',
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: __( "Media Management", "multivendorx" ),
            hint: __( "Manage the media files sellers can upload.", "multivendorx" ),
        },
        {
            key: 'is_upload_files',
            type: 'checkbox',
            label: __( 'Upload Media Files', 'multivendorx' ),
            desc:  __( 'Let sellers upload media files such as images, videos, eBooks, and music to enhance their product listings.', 'multivendorx' ),
            options: [
                {
                    key: "is_upload_files",
                    value: "is_upload_files",
                }
            ],
            look:'toggle',
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: __( "SKU Generation", "catalogx" ),
            hint: __( "Control how SKUs are handled for products.", "multivendorx" ),
        },
        {
            key: "sku_generator_simple",
            type: "select",
            label: __( 'SKU Management for Simple & Parent Products', 'multivendorx' ),
            desc: __( 'Choose how SKUs for simple, external, or parent products are generated:', 'multivendorx' ),
            options: [
                {
                    key: "choose_options",
                    label: __( 'Choose options', 'multivendorx' ),
                    value: 'choose_options'
                },
                {
                    key: "never",
                    label: __( 'Never (let me set them )', 'multivendorx' ),
                    value: 'never'
                },
                {
                    key: "slugs",
                    label: __( 'Using the product slug (name )', 'multivendorx' ),
                    value: 'slugs'
                },
                {
                    key: "ids",
                    label: __( 'Using the product ID )', 'multivendorx' ),
                    value: 'ids'
                },
            ],
        },
        {
            key: "sku_generator_variation",
            type: "select",
            label: __( 'SKU Management for Product Variations', 'multivendorx' ),
            desc: __( 'Define how SKUs for product variations will be generated:', 'multivendorx' ),
            options: [
                {
                    key: "choose_options",
                    label: __( 'Choose options', 'multivendorx' ),
                    value: 'choose_options'
                },
                {
                    key: "never",
                    label: __( 'Never (let me set them )', 'multivendorx' ),
                    value: 'never'
                },
                {
                    key: "slugs",
                    label: __( 'Using the product slug (name )', 'multivendorx' ),
                    value: 'slugs'
                },
                {
                    key: "ids",
                    label: __( 'Using the product ID )', 'multivendorx' ),
                    value: 'ids'
                },
            ],
        },
        {
            key: "sku_generator_attribute_spaces",
            type: "select",
            label: __( 'Replace Spaces in Attribute Names for SKUs', 'multivendorx' ),
            desc: __( 'Choose whether to replace spaces in attribute names when generating SKUs:', 'multivendorx' ),
            options: [
                {
                    key: "choose_options",
                    label: __( 'Choose options', 'multivendorx' ),
                    value: 'choose_options'
                },
                {
                    key: "no",
                    label: __( 'Do not replace spaces in attribute names.', 'multivendorx' ),
                    value: 'no'
                },
                {
                    key: "underscore",
                    label: __( 'Replace spaces with underscores', 'multivendorx' ),
                    value: 'underscore'
                },
                {
                    key: "dash",
                    label: __( 'Replace spaces with dashes / hyphens', 'multivendorx' ),
                    value: 'dash'
                },
                {
                    key: "none",
                    label: __( 'Remove spaces from attribute names', 'multivendorx' ),
                    value: 'none'
                },
            ],
        },
    ]
}