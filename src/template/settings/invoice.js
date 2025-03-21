import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-vendor-invoice',
    priority: 17,
    name: __( 'Invoice', 'multivendorx' ),
    desc: __( "Select the PDF outupt mode.", 'multivendorx' ),
    icon: 'adminLib-clock2',
    submitUrl: 'settings',
    modal: [
        {
            key: "pdf_output",
            type: "select",
            label: __( 'PDF Output', 'multivendorx' ),
            desc: __( 'Select the PDF output mode.', 'multivendorx' ),
            options: [
                {
                    key: "download",
                    label: __( 'Download the PDF', 'multivendorx' ),
                    value: "download_pdf",
                },
                {
                    key: "inline",
                    label: __( 'Open PDF in browser', 'multivendorx' ),
                    value: "inline_pdf",
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "pdf_size",
            type: "select",
            label: __( 'PDF Size', 'multivendorx' ),
            desc: __( 'Select the PDF size.', 'multivendorx' ),
            options: [
                {
                    key: "a4",
                    label: __( 'A4', 'multivendorx' ),
                    value: "a4",
                },
                {
                    key: "letter",
                    label: __( 'Letter', 'multivendorx' ),
                    value: "letter",
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "pdf_orientation",
            type: "select",
            label: __( 'PDF Orientation', 'multivendorx' ),
            desc: __( 'Select the pdf orientation.', 'multivendorx' ),
            options: [
                {
                    key: "portrait",
                    label: __( 'Portrait', 'multivendorx' ),
                    value: "portrait",
                },
                {
                    key: "landscape",
                    label: __( 'Landscape', 'multivendorx' ),
                    value: "landscape",
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "attach_to_email_input",
            type: "select",
            label: __( 'Attach to email', 'multivendorx' ),
            desc: __( 'Select Order status to attach invoice.', 'multivendorx' ),
            options: appLocalizer.available_emails_filtered,
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "choose_invoice_template",
            type: "select",
            label: __( 'Select default invoice template', 'multivendorx' ),
            options: [
                {
                    key: "template1",
                    label: __( 'Template1', 'multivendorx' ),
                    value: "template1",
                },
                {
                    key: "template2",
                    label: __( 'Template2', 'multivendorx' ),
                    value: "template2",
                },
                {
                    key: "template3",
                    label: __( 'Template3', 'multivendorx' ),
                    value: "template3",
                },
                {
                    key: "template4",
                    label: __( 'Template4', 'multivendorx' ),
                    value: "template4",
                },
                {
                    key: "template5",
                    label: __( 'Template5', 'multivendorx' ),
                    value: "template5",
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key:  'invoice_template_1',
            type:  'blocktext',
            valuename: __( '', 'multivendorx'  ),
            blocktext: __( `<br><a target="_blank" class="mvx-preview-pdf-tpl" href="#">${__( 'Click here to see preferred template view', 'multivendorx' )}</a>`, 'multivendorx'  ),
            dependent: {
                key: "choose_invoice_template",
                value:"template1",
            },
        },
        {
            key:  'invoice_template_2',
            type:  'blocktext',
            valuename: __( '', 'multivendorx'  ),
            blocktext: __( `<br><a target="_blank" class="mvx-preview-pdf-tpl" href="#">${__( 'Click here to see preferred template view', 'multivendorx' )}</a>`, 'multivendorx'  ),
            dependent: {
                key: "choose_invoice_template",
                value:"template2",
            },
        },
        {
            key:  'invoice_template_3',
            type:  'blocktext',
            valuename: __( '', 'multivendorx'  ),
            blocktext: __( `<br><a target="_blank" class="mvx-preview-pdf-tpl" href="#">${__( 'Click here to see preferred template view', 'multivendorx' )}</a>`, 'multivendorx'  ),
            dependent: {
                key: "choose_invoice_template",
                value:"template3",
            },
        },
        {
            key:  'invoice_template_4',
            type:  'blocktext',
            valuename: __( '', 'multivendorx'  ),
            blocktext: __( `<br><a target="_blank" class="mvx-preview-pdf-tpl" href="#">${__( 'Click here to see preferred template view', 'multivendorx' )}</a>`, 'multivendorx'  ),
            dependent: {
                key: "choose_invoice_template",
                value:"template4",
            },
        },
        {
            key:  'invoice_template_5',
            type:  'blocktext',
            valuename: __( '', 'multivendorx'  ),
            blocktext: __( `<br><a target="_blank" class="mvx-preview-pdf-tpl" href="#">${__( 'Click here to see preferred template view', 'multivendorx' )}</a>`, 'multivendorx'  ),
            dependent: {
                key: "choose_invoice_template",
                value:"template5",
            },
        },
        {
            key: "pdf_invoice_fields",
            label: __( 'Invoice fields', 'multivendorx'  ),
            type: "checkbox",
            options: [
                {
                    key: "is_vendor_add_disclaimer",
                    value: "is_vendor_add_disclaimer",
                    label: __( 'Vendor disclaimer', 'multivendorx' )
                },
                {
                    key: "is_vendor_add_gst_no",
                    value: "is_vendor_add_gst_no",
                    label: __( 'Vendor GST number', 'multivendorx' )
                },
                {
                    key: "is_vendor_add_tax_no",
                    value: "is_vendor_add_tax_no",
                    label: __( 'Vendor TAX Number', 'multivendorx' )
                },
                {
                    key: "is_vendor_add_digital_sign",
                    value: "is_vendor_add_digital_sign",
                    label: __( 'Vendor Signature', 'multivendorx' )
                },
                {
                    key: "is_vendor_add_shop_policy",
                    value: "is_vendor_add_shop_policy",
                    label: __( 'Vendor policies', 'multivendorx' )
                },
                {
                    key: "is_invoice_no",
                    value: "is_invoice_no",
                    label: __( 'Invoice number', 'multivendorx' )
                }
            ],
            select_deselect: true,
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "invoice_no_format",
            type: "text",
            label: __( 'Invoice number format', 'multivendorx'  ),
            desc: __( 'Add alphanumeric invoice number prefix or use YEAR, MONTH, \'-\' & \'\/\'.', 'multivendorx'  ),
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "company_logo",
            type: "file",
            label: __( 'Company logo', 'multivendorx'  ),
            desc: __( 'Upload brand image as logo', 'multivendorx' ),
            height: 75,
            width: 75,
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'admin_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx'  ),
            blocktext: __( "Admin Template Settings", 'multivendorx'  ),
        },
        {
            key: "pdf_invoice_admin_fields",
            label: __( 'Invoice admin fields', 'multivendorx'  ),
            type: "checkbox",
            select_deselect: true,
            options: [
                {
                    key: "is_subtotal_admin",
                    value: "is_subtotal_admin",
                    label: __( 'Subtotal', 'multivendorx' )
                },
                {
                    key: "is_discount_admin",
                    value: "is_discount_admin",
                    label: __( 'Discount', 'multivendorx' )
                },
                {
                    key: "is_tax_admin",
                    value: "is_tax_admin",
                    label: __( 'Tax', 'multivendorx' )
                },
                {
                    key: "is_shipping_admin",
                    value: "is_shipping_admin",
                    label: __( 'Shipping', 'multivendorx' )
                },
                {
                    key: "is_payment_method_admin",
                    value: "is_payment_method_admin",
                    label: __( 'Payment Method', 'multivendorx' )
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "term_and_conditions_admin",
            type: "textarea",
            label: __( 'Term and conditions', 'multivendorx'  ),
            desc: __( "", 'multivendorx'  ),
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "is_customer_note_admin",
            label: __( 'Show Customer Note', 'multivendorx'  ),
            type: "checkbox",
            desc: __( "Show customer note for admin PDF.", 'multivendorx' ),
            options: [
                {
                    key: "is_customer_note_admin",
                    value: "is_customer_note_admin"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "admin_gst_no",
            type: "text",
            label: __( 'GST No', 'multivendorx'  ),
            desc: __( 'Set admin GST number for admin invoice only', 'multivendorx'  ),
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "admin_tax_no",
            type: "text",
            label: __( 'TAX No', 'multivendorx'  ),
            desc: __( 'Set admin TAX number for admin invoice only', 'multivendorx'  ),
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "admin_signature",
            type: "file",
            label: __( 'Admin signature', 'multivendorx'  ),
            desc: __( 'Upload image as admin signature', 'multivendorx' ),
            height: 75,
            width: 75,
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'customer_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx'  ),
            blocktext: __( "Customer Template Settings", 'multivendorx'  ),
        },
        {
            key: "customer_attach_to_email",
            type: "select",
            label: __( 'Attach invoice to customer to email', 'multivendorx' ),
            desc: __( 'Select Order status to attach invoice.', 'multivendorx' ),
            options: appLocalizer.available_emails_filtered,
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "is_customer_download_invoice",
            label: __( 'Choose Order Types for Customer Invoice Access', 'multivendorx'  ),
            type: "checkbox",
            select_deselect: true,
            options: [
                {
                    key: "order_invoice_customer",
                    value: "order_invoice_customer",
                    label: __( 'Main Order Invoice', 'multivendorx' )
                },
                {
                    key: "suborder_invoice_customer",
                    value: "suborder_invoice_customer",
                    label: __( 'Suborder Invoice', 'multivendorx' )
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "status_to_download_invoice",
            type: "multi-select",
            label: __( 'Choose Order Status for Customer Invoice Access', 'multivendorx' ),
            desc: __( 'Choose the order statuses for customer invoice downloads.', 'multivendorx' ),
            dependent: {
                key: "is_customer_download_invoice",
                set:true,
            },
            options: appLocalizer.order_statuses,
            select_deselect: true,
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "pdf_invoice_customer_fields",
            label: __( 'Invoice customer fields', 'multivendorx'  ),
            type: "checkbox",
            select_deselect: true,
            options: [
                {
                    key: "is_subtotal_customer",
                    value: "is_subtotal_customer",
                    label: __( 'Subtotal', 'multivendorx' )
                },
                {
                    key: "is_discount_customer",
                    value: "is_discount_customer",
                    label: __( 'Discount', 'multivendorx' )
                },
                {
                    key: "is_tax_customer",
                    value: "is_tax_customer",
                    label: __( 'Tax', 'multivendorx' )
                },
                {
                    key: "is_shipping_customer",
                    value: "is_shipping_customer",
                    label: __( 'Shipping', 'multivendorx' )
                },
                {
                    key: "is_payment_method_customer",
                    value: "is_payment_method_customer",
                    label: __( 'Payment Method', 'multivendorx' )
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "term_and_conditions_customer",
            type: "textarea",
            label: __( 'Term and conditions', 'multivendorx'  ),
            desc: __( "", 'multivendorx'  ),
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "is_customer_note_customer",
            label: __( 'Show Customer Note', 'multivendorx'  ),
            type: "checkbox",
            desc: __( "Show customer note for customer PDF.", 'multivendorx' ),
            options: [
                {
                    key: "is_customer_note_customer",
                    value: "is_customer_note_customer"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key: "is_packing_slip_customer",
            label: __( 'Enable Packing Slip', 'multivendorx'  ),
            type: "checkbox",
            desc: __( "Enable packing slip.", 'multivendorx' ),
            options: [
                {
                    key: "is_packing_slip_customer",
                    value: "is_packing_slip_customer"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        {
            key:  'vendor_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx'  ),
            blocktext: __( "Vendor Template Settings", 'multivendorx'  ),
        },
        {
            key: "pdf_invoice_vendor_fields",
            type: "checkbox",
            select_deselect: true,
            label: __( 'Invoice vendor fields', 'multivendorx' ),
            options: [
                {
                    key: "is_subtotal_vendor",
                    label: __( 'Subtotal', 'multivendorx' ),
                    value: "is_subtotal_vendor",
                },
                {
                    key: "is_discount_vendor",
                    label: __( 'Discount', 'multivendorx' ),
                    value: "is_discount_vendor",
                },
                {
                    key: "is_tax_vendor",
                    label: __( 'Tax', 'multivendorx' ),
                    value: "is_tax_vendor",
                },
                {
                    key: "is_shipping_vendor",
                    label: __( 'Shipping', 'multivendorx' ),
                    value: "is_shipping_vendor",
                },
                {
                    key: "is_payment_method_vendor",
                    label: __( 'Payment Method', 'multivendorx' ),
                    value: "is_payment_method_vendor",
                }
            ],
            proSetting:true,
            moduleEnabled: 'invoice',
        },
        
    ]
}