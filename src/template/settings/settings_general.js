import { __ } from '@wordpress/i18n';

export default {
    id: 'settings_general_tab',
    priority: 1,
    name: __("General",'multivendorx'),
    desc: __( 'Configure the basic setting of the marketplace.', 'multivendorx' ),
    icon: 'adminLib-general-tab',
    submitUrl: 'settings',
    modal: [
        {
            key: "approve_vendor",
            type: "settingToggle",
            label: __( 'Vendor approval', 'multivendorx' ),
            desc: __( 'Decide how you want to approve new vendors for your marketplace: <li>Manual Approval: Review and approve vendors manually. <li>Automatic Approval: Automatically approve vendors without review.', 'multivendorx' ),
            options: [
                {
                    name:'approve_vendor',
                    key:'manually',
                    label: __('Manually', 'multivendorx'),
                    value: 'manually'
                },
                {
                    name:'approve_vendor',
                    key:'automatically',
                    label: __('Automatically', 'multivendorx'),
                    value: 'automatically'
                }
            ],
        },
        {
            key: 'vendors_backend_access',
            type: 'checkbox',
            label: __( "Vendor backend access", 'multivendorx' ),
            desc: __('Unlock an all-in-one vendor dashboard that allows vendors to manage everything in one place', 'multivendorx'),
            options: [
                {
                    key: "vendors_backend_access",
                    value: 'vendors_backend_access'
                },
            ],
            look: 'toggle',
            proSetting: true,
        },
        {
            key: 'display_product_seller',
            type: 'checkbox',
            label: __( "Display vendor name on products", 'multivendorx' ),
            desc: __("show the vendor’s name on their product listings. This helps customers identify who they’re buying from.", 'multivendorx'),
            options: [
                {
                    key: "display_product_seller",
                    value: 'display_product_seller'
                },
            ],
            look: 'toggle',
        },
        {
            key: 'category_pyramid_guide',
            type: 'checkbox',
            label: __( "Category assistance (CPG)", 'multivendorx' ),
            desc: __("Help vendors categorize their products accurately with the Category Pyramid Guide.", 'multivendorx'),
            options: [
                {
                    key: "category_pyramid_guide",
                    value: 'category_pyramid_guide'
                },
            ],
            look: 'toggle',
        },
        {
            key: 'is_customer_support_details',
            type: 'checkbox',
            label: __( "Customer support information", 'multivendorx' ),
            desc: __("Display customer support details on the \"Thank You\" page and in new order confirmation emails. This can improve customer satisfaction by making support easily accessible.", 'multivendorx'),
            options: [
                {
                    key: "is_customer_support_details",
                    value: 'is_customer_support_details'
                },
            ],
            look: 'toggle',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'vendor_list_page',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( "Use the <code>[mvx_vendorlist]</code> shortcode to display vendor's list on your site <a href='https://www.w3schools.com'>Learn More</a>", 'multivendorx' ),
        },
        {
            key: 'registration_page',
            type: 'select',
            label: __( 'Registration page', 'multivendorx' ),
            desc: __( 'Select the page on which you have inserted <code>[vendor_registration]</code> shortcode.This is the page where new vendors can sign up.', 'multivendorx' ),
            options: appLocalizer.pageList,
        },
        {
            key: 'vendor_dashboard_page',
            type: 'select',
            label: __( 'Vendor dashboard page', 'multivendorx' ),
            desc: __( 'Select the page on which you have inserted <code>[mvx_vendor]</code> shortcode. This will be the main dashboard page for your vendors to manage their store.', 'multivendorx' ),
            options: appLocalizer.pageList,
        },
        {
            key: "mvx_tinymce_api_section",
            type: "text",
            label: __( 'TinyMCE Api', 'multivendorx' ),
            desc: __( 'Set TinyMCE Api key <a href="https://www.tiny.cloud/blog/how-to-get-tinymce-cloud-up-in-less-than-5-minutes/" target="_blank">Click here to generate key</a> to enable the text editor for vendors. This allows them to format their product descriptions and other content with ease.', 'multivendorx' ),
        },
        {
            key: 'avialable_shortcodes',
            type: 'shortCode-table',
            label: __( 'Avialable shortcodes', 'multivendorx' ),
            desc: __('', "multivendorx"),
            optionLabel: [
                'Shortcodes',
                'Description',
            ],
            option: [
                {
                    key: '',
                    label: '[mvx_vendor]',
                    desc: __('Enables you to create a seller dashboard ', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[vendor_registration]',
                    desc: __('Creates a page where the vendor registration form is available', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[vendor_coupons]',
                    desc: __('Lets you view  a brief summary of the coupons created by the seller and number of times it has been used by the customers', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_recent_products]',
                    desc: __('Allows you to glance at the recent products added by seller', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_products]',
                    desc: __('Displays the products added by seller', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_featured_products]',
                    desc: __('Exhibits featured products added by the seller', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_sale_products]',
                    desc: __('Allows you to see the products put on sale by a seller', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_top_rated_products]',
                    desc: __('Displays the top rated products of the seller', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_best_selling_products]',
                    desc: __('Presents you the option of viewing the best selling products of the vendor', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_product_category]',
                    desc: __('Lets you see the product categories used by the vendor', 'multivendorx'),
                },
                {
                    key: '',
                    label: '[mvx_vendorslist]',
                    desc:  __('Shows customers a list of available seller.', 'multivendorx'),
                },
            ]       
        },
    ]
}