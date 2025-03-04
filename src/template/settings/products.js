import { __ } from '@wordpress/i18n';

export default {
    id: 'products',
    priority: 5,
    name: __('Products', 'multivendorx'),
    desc:  __("Select the type of product that best suits your marketplace.", 'multivendorx'),
    icon: 'adminLib-warehousing-icon',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: 'type_options',
            type: 'checkbox',
            label: __( 'Type options', 'multivendorx' ),
            class: 'mvx-toggle-checkbox',
            desc:  __('Select if the product is non-tangible or downloadable.', 'multivendorx'),
            options: [
                {
                    key: "virtual",
                    label: __('Virtual', 'multivendorx'),
                    value: "virtual",
                },
                {
                    key: "downloadable",
                    label: __('Downloadable', 'multivendorx'),
                    value: "downloadable",
                }
            ],
            select_deselect: true,
        },
        {
            key: 'products_fields',
            type: 'checkbox',
            label: __( 'Product Fields ', 'multivendorx' ),
            class: 'mvx-toggle-checkbox',
            options: [
                {
                    key: "general",
                    label: __('General', 'multivendorx'),
                    value: "general",
                },
                {
                    key: "inventory",
                    label: __('Inventory', 'multivendorx'),
                    value: "inventory",
                },
                {
                    key: "linked_product",
                    label: __('Linked Product', 'multivendorx'),
                    value: "linked_product",
                },
                {
                    key: "attribute",
                    label: __('Attribute', 'multivendorx'),
                    value: "attribute",
                },
                {
                    key: "advanced",
                    label: __('Advance', 'multivendorx'),
                    value: "advanced",
                },
                {
                    key: "policies",
                    label: __('Policies', 'multivendorx'),
                    value: "policies",
                },
                {
                    key: "product_tag",
                    label: __('Product Tag', 'multivendorx'),
                    value: "product_tag",
                },
                {
                    key: "GTIN",
                    label: __('GTIN', 'multivendorx'),
                    value: "GTIN",
                },
            ],
            select_deselect: true,
        },
    ]
}