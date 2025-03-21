import { __ } from '@wordpress/i18n';

export default {
    id: 'seller-dashboard',
    priority: 3,
    name: __( 'Seller Dashboard', 'multivendorx' ),
    desc: __( 'Manage the appearance of your seller\'s dashboard.', 'multivendorx' ),
    icon: 'adminLib-clock2',
    submitUrl: 'settings',
    modal: [
        {
            key: 'mvx_new_dashboard_site_logo',
            type: 'file',
            label: __( 'Branding Logo', 'multivendorx' ),
            width:75,
            height:75,
            desc: __( 'Upload brand image as logo', 'multivendorx' ),
        },
        {
            key: 'vendor_color_scheme_picker',
            type: 'radio-color',
            label: __( 'Color Scheme', 'multivendorx' ),
            desc: __( 'Select your prefered seller dashboard colour scheme', 'multivendorx' ),
            options:[
                {
                    key:'outer_space_blue',
                    label: __( 'Outer Space', 'multivendorx' ),
                    value:'outer_space_blue',
                    color:['#202528', '#333b3d', '#3f85b9', '#316fa8']
                },
                {
                    key:'green_lagoon',
                    label: __( 'Green Lagoon', 'multivendorx' ),
                    value:'green_lagoon',
                    color:['#171717', '#212121', '#009788','#00796a']
                },
                {
                    key:'old_west',
                    label: __( 'Old West', 'multivendorx' ),
                    value:'old_west',
                    color:['#46403c', '#59524c', '#c7a589', '#ad8162']
                },
                {
                    key:'wild_watermelon',
                    label: __( 'Wild Watermelon', 'multivendorx' ),
                    value:'wild_watermelon',
                    color:['#181617', '#353130', '#fd5668', '#fb3f4e']
                }
            ]
        },
        {
            key: 'setup_wizard_introduction',
            type: 'textarea',
            label: __( 'Vendor Setup wizard Introduction Message', 'multivendorx' ),
            desc: __( 'Welcome vendors with creative onboard messages', 'multivendorx' ),
        },
        {
            key: 'vendor_deactivation_enabled',
            type: 'checkbox',
            label: __( 'Enable Vendor Profile Deactivation', 'multivendorx' ),
            desc:sprintf(__( 'Allows vendors to request the deactivation of their profiles. To know more, please <a href=\'%s\'>click here</a>.', 'multivendorx' ), 'https://multivendorx.com/docs/knowledgebase/vendor-account-deactivation-flow/' ),
            options: [
                {
                    key: 'vendor_deactivation_enabled',
                    value: 'vendor_deactivation_enabled'
                },
            ],
            look: 'toggle',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key: 'mvx_vendor_announcements_endpoint',
            type: 'text',
            label: __( 'Vendor Announcements Endpoint', 'multivendorx' ), 
            desc: __( 'Set endpoint for vendor announcements page', 'multivendorx' ),
            placeholder: __( 'vendor-announcements', 'multivendorx' ),
        },
        {
            key: 'mvx_store_settings_endpoint',
            type: 'text',
            label: __( 'Storefront Endpoint', 'multivendorx' ),
            desc: __( 'Used as site logo on vendor dashboard pages', 'multivendorx' ),
            placeholder:__( 'storefront', 'multivendorx' ),
        },
        {
            key: 'mvx_profile_endpoint',
            type: 'text',
            label: __( 'Seller Profile Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor profile management page', 'multivendorx' ),
            placeholder:__( 'profile', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_policies_endpoint',
            type: 'text',
            label: __( 'Seller Policies Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor policies page', 'multivendorx' ),
            placeholder:__( 'vendor-policies', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_billing_endpoint',
            type: 'text',
            label: __( 'Seller Billing Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor billing page', 'multivendorx' ),
            placeholder:__( 'vendor-billing', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_shipping_endpoint',
            type: 'text',
            label: __( 'Vendor Shipping Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor shipping page', 'multivendorx' ),
            placeholder:__( 'vendor-shipping', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_report_endpoint',
            type: 'text',
            label: __( 'Seller Report Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor report page', 'multivendorx' ),
            placeholder:__( 'vendor-report', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_banking_overview_endpoint',
            type: 'text',
            label: __( 'Banking Overview Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor banking overview page', 'multivendorx' ),
            placeholder:__( 'banking-overview', 'multivendorx' ),
        },
        {
            key: 'mvx_add_product_endpoint',
            type: 'text',
            label: __( 'Add Product Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for add new product page', 'multivendorx' ),
            placeholder:__( 'add-product', 'multivendorx' ),
        },
        {
            key: 'mvx_edit_product_endpoint',
            type: 'text',
            label: __( 'Edit Product Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for edit product page', 'multivendorx' ),
            placeholder:__( 'edit-product', 'multivendorx' ),
        },
        {
            key: 'mvx_products_endpoint',
            type: 'text',
            label: __( 'Products List Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for products list page', 'multivendorx' ),
            placeholder:__( 'products', 'multivendorx' ),
        },
        {
            key: 'mvx_add_coupon_endpoint',
            type: 'text',
            label: __( 'Add Coupon Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for add new coupon page', 'multivendorx' ),
            placeholder:__( 'add-coupon', 'multivendorx' ),
        },
        {
            key: 'mvx_coupons_endpoint',
            type: 'text',
            label: __( 'Coupons List Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for coupons list page', 'multivendorx' ),
            placeholder:__( 'coupons', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_orders_endpoint',
            type: 'text',
            label: __( 'Vendor Orders Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor orders page', 'multivendorx' ),
            placeholder:__( 'vendor-orders', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_withdrawal_endpoint',
            type: 'text',
            label: __( 'Vendor Widthdrawals Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor widthdrawals page', 'multivendorx' ),
            placeholder:__( 'vendor-withdrawal', 'multivendorx' ),
        },
        {
            key: 'mvx_transaction_details_endpoint',
            type: 'text',
            label: __( 'Transaction Details Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for transaction details page', 'multivendorx' ),
            placeholder:__( 'transaction-details', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_knowledgebase_endpoint',
            type: 'text',
            label: __( 'Seller Knowledgebase Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor knowledgebase page', 'multivendorx' ),
            placeholder:__( 'vendor-knowledgebase', 'multivendorx' ),
        },
        {
            key: 'mvx_vendor_tools_endpoint',
            type: 'text',
            label: __( 'Seller Tools Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor tools page', 'multivendorx' ),
            placeholder:__( 'vendor-tools', 'multivendorx' ),
        },
        {
            key: 'mvx_products_qna_endpoint',
            type: 'text',
            label: __( 'Seller Products Q&As Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor products Q&As page', 'multivendorx' ),
            placeholder:__( 'products-qna', 'multivendorx' ),
        },
        {
            key: 'mvx_refund_req_endpoint',
            type: 'text',
            label: __( 'Refund Endpoint', 'multivendorx' ),
            desc: __( 'Set endpoint for refund page', 'multivendorx' ),
            placeholder:__( 'refund-request', 'multivendorx' ),
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key: 'mvx_vendor_dashboard_custom_css',
            type: 'textarea',
            label: __( 'Custom CSS', 'multivendorx' ),
            desc: __( 'Apply custom CSS to change dashboard design', 'multivendorx' ),
        },
        // Premimum features
        // Required module Name : Seller Identity Verification
        {
            key: 'mvx_vendor_verification_endpoint',
            type: 'text',
            label: __( 'Vendor Verification', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor verification page', 'multivendorx' ),
            placeholder:__( 'vendor-verification', 'multivendorx' ),
            moduleEnabled: 'identity-verification',
            proSetting: true,
        },
        // Required module Name : Import Export
        {
            key: 'mvx_product_export_endpoint',
            type: 'text',
            label: __( 'Import export', 'multivendorx' ),
            desc: __( 'Set endpoint for import export', 'multivendorx' ),
            placeholder:__( 'product-export', 'multivendorx' ),
            moduleEnabled: 'import-export',
            proSetting: true,
        },
        // Required module Name : Invoice & Packing Slip
        {
            key: 'mvx_vendor_pdf_invoice_endpoint',
            type: 'text',
            label: __( 'PDF Invoice', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor pdf page', 'multivendorx' ),
            placeholder:__( 'pdf-invoice', 'multivendorx' ),
            moduleEnabled: 'invoice',
            proSetting: true,
        },
        // Required module Name : Staff Manager
        {
            key: 'mvx_add_vendor_staff_endpoint',
            type: 'text',
            label: __( 'Add Vendor Staff', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor add staff page', 'multivendorx' ),
            placeholder:__( 'add-staff', 'multivendorx' ),
            moduleEnabled: 'staff-manager',
            proSetting: true,
        },
        {
            key: 'mvx_manage_vendor_staff_endpoint',
            type: 'text',
            label: __( 'Manage Vendor Staff', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor manage staff page', 'multivendorx' ),
            placeholder:__( 'manage-staff', 'multivendorx' ),
            moduleEnabled: 'staff-manager',
            proSetting: true,
        },
        // Required module Name : Business Hours
        {
            key: 'mvx_business_hours_endpoint',
            type: 'text',
            label: __( 'Business Hours', 'multivendorx' ),
            desc: __( 'Set endpoint for business hours page', 'multivendorx' ),
            placeholder:__( 'business-hours', 'multivendorx' ),
            moduleEnabled: 'business-hours',
            proSetting: true,
        },
        // Required module Name : Vacation
        {
            key: 'mvx_vendor_vacation_endpoint',
            type: 'text',
            label: __( 'Vendor Vacation', 'multivendorx' ),
            desc: __( 'Set endpoint for vendor vacation page', 'multivendorx' ),
            placeholder:__( 'vendor-vacation', 'multivendorx' ),
            moduleEnabled: 'vacation',
            proSetting: true,
        },
        // Required module Name : Live Chat
        {
            key: 'mvx_live_chat_endpoint',
            type: 'text',
            label: __( 'Live Chat', 'multivendorx' ),
            desc: __( 'Set endpoint for live chat page', 'multivendorx' ),
            placeholder:__( 'live-chat', 'multivendorx' ),
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
    ]
}