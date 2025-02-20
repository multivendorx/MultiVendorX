export default {
    id: 'simple',
    priority: 1,
    name: "Simple",
    desc: "Drag-and-drop to create and customize single product page elements.",
    icon: 'adminLib-settings',
    submitUrl: 'save-settings',
    modal: [
        {
            key: 'separator_content',
            type: 'section',
            desc: "BasicInput",
            hint: "hinttttt"
        },
        {
            key: "sample_text",
            type: "text",
            label: 'Sample Text Box', 
            desc: "This is a simple text box (text, url, email, password, number)",
            placeholder: "write something",
        },
        {
            key: "sample_parameter_text",
            type: "text",
            label: 'Sample Text Box with parameter', 
            desc: "This is a simple text box with parameter",
            parameter: 'days',
            proSetting: true
        },
        {
            key: "sample_normal_file",
            type: "normalfile",
            label: 'Sample normal file', 
            desc: "This is a simple file input",
        },
        {
            key: "sample_color",
            type: "color",
            label: 'Sample Color', 
            desc: "This is a simple color",
        },
        {
            key: "tinymce_api_key",
            type: "text",
            label: 'Tinymce api key', 
            desc: "This is the tinymce api key",
            placeholder: "write something",
        },
        {
            key: "sample_range",
            type: "range",
            label: 'Sample Range',
            inputLabel: "Range Input",
            rangeUnit: 'px' 
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "button",
        },
        {
            key: "sample_button",
            type: "button",
            label: 'Sample button', 
            desc: "This is a simple button",
            placeholder: "write something",
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "CalenderInput",
        },
        {
            key: "sample_multiple_calender_range",
            type: "calender",
            label: 'Sample multiple calender with range', 
            desc: "This is a simple calender",
            multiple: true,
            range: true
        },
        {
            key: "sample_multiple_calender",
            type: "calender",
            label: 'Sample multiple calender', 
            desc: "This is a simple calender",
            multiple: true,
        },
        {
            key: "sample_single_calender",
            type: "calender",
            label: 'Sample single calender', 
            desc: "This is a simple calender",
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "FileInput(wordpress)",
        },
        {
            key: "sample_file",
            type: "file",
            label: 'Sample file', 
            desc: "This is a simple file input",
            // height: 100,
            width: 100
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "MapsInput",
        },
        {
            key: "sample_map",
            type: "map",
            label: 'Sample map', 
            desc: "This is a simple map",
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "GoogleMap",
        },
        // {
        //     key: "sample_google_map",
        //     type: "google-map",
        //     label: 'Sample Google Map',
        //     center: { lat: 88.54282, lng: 22.77024 },// set from settings and api from appLocalizer
        //     desc: "This is a Google Map"
        // },
        {
            key: 'separator_content',
            type: 'section',
            desc: "MultiCheckbox",
        },
        {
            key: 'sample_checkbox',
            type: 'checkbox',
            label: 'Single Checkbox',
            desc: 'Redirect users to the homepage when they click on the cart or checkout page. To customize the redirection to a different page, an upgrade to Pro <a href="https://multivendorx.com/woocommerce-request-a-quote-product-catalog/" target="_blank">WooCommerce Catalog Enquiry Pro</a>.',
            options: [
                {
                    key: "sample_checkbox",
                    hints: "If enabled, non-logged-in users can't access the enquiry flow.",
                    value: 'sample_checkbox'
                },
            ],
            proSetting: true,
            look: 'toggle',
            moduleEnabled: 'demo1',
        },
        {
            key: "sync-course-options",
            type: "checkbox",
            desc: "", 
            label: "Course information mapping", 
            select_deselect: true,
            options: [
                {
                    key: "sync_courses_category",
                    label: 'Course categories', 
                    hints: "Scan the entire Moodle course category structure and synchronize it with the WordPress category listings.", 
                    value: "sync_courses_category",
                },
                {
                    key: "sync_courses_sku",
                    label: 'Course ID number - Product SKU', 
                    hints: "Retrieves the course ID number and assigns it as the product SKU.", 
                    value: "sync_courses_sku",
                    proSetting: true,
                },
                {
                    key: "sync_image",
                    label: 'Course image', 
                    hints: "Copies course images and sets them as WooCommerce product images.", 
                    value: "sync_image",
                    proSetting: true,
                },
            ]
        },
        {
            key: 'sample_multi_checkbox_select_deselect',
            type: 'checkbox',
            label: 'Product Status (Multiple with select-deselect button)',
            class: 'woo-toggle-checkbox',
            desc:  "Lead time informs customers when a product will be available again. This setting lets you choose which stock statuses will display the restock estimate.",
            options: [
                {
                    key: "outofstock",
                    label: "Out of stock",
                    value: "outofstock",
                    hints: "outofstock"
                },
                {
                    key: "onbackorder",
                    label: "On backorder",
                    value: "onbackorder",
                    hints: "onbackorder",
                }
            ],
            select_deselect: true,
            right_content: true
        },
        {
            key: 'notify_me_button',
            type: 'stock-alert-checkbox',
            label: "In-Stock notify me button ", 
            desc: "This option allows customers to subscribe for automatic stock notifications.", 
            options: [
                {
                    key: "notify_me_button",
                    label: "",
                    value: "notify_me_button"
                }
            ],
            look: 'toggle',
            dependentPlugin: appLocalizer.stock_alert_open
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "MultiNumInput",
        },
        {
            key: "sample_multinumber",
            type: "multi-number",
            label: 'Commission Value', 
            desc: "This is a simple multi number input",
            options: [
                {
                    key: "option1",
                    type: 'number',
                    label: 'Fixed',
                    value: 'option1'
                },
                {
                    key: "option2",
                    type: 'number',
                    label: 'Percentage',
                    value: 'option2'
                },
            ],
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "RadioInput",
        },
        {
            key: "sample_radio",
            type: "radio",
            label: 'Set Schedule', 
            desc: "This is a simple radio input",
            options: [
                {
                    key: "option1",
                    label: 'Weekly',
                    value: 'option1'
                },
                {
                    key: "option2",
                    label: 'Daily',
                    value: 'option2'
                },
                {
                    key: "option3",
                    label: 'Monthly',
                    value: 'option3'
                },
            ],
        },
        {
            key: "sample_radio_select",
            type: "radio-select",
            label: "Store Header",
            desc: "Select store banner style",
            options: [
                {
                    key: "option1",
                    label: "Outer Space",
                    value: 'template1',
                    color: appLocalizer.template1,
                },
                {
                    key: "option2",
                    label: 'Green Lagoon',
                    value: "template2",
                    color: appLocalizer.template1,
                },
            ],
        },
        {
            key: "sample_radio_color",
            type: "radio-color",
            label: 'Sample Radio color(radio with color)', 
            desc: "This is a simple radio color input",
            options: [
                {
                    key: "option1",
                    value: 'option1',
                    color: ['#202528', '#333b3d','#3f85b9', '#316fa8'],
                },
                {
                    key: "option2",
                    value: 'option2',
                    color: ['#171717', '#212121', '#009788','#00796a'],
                },
            ],
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "SelectInput",
        },
        {
            key: "sample_select",
            type: "select",
            label: 'Sample select', 
            desc: "Specify the exact amount or percentage to be deducted from the total order value",
            options: [
                {
                    key: "option1",
                    label: 'Cart',
                    value: 'option1'
                },
                {
                    key: "option2",
                    label: 'Checkout',
                    value: 'option2'
                },
            ],
        },
        {
            key: "sample_multi_select",
            type: "multi-select",
            label: 'Sample Multi Select', 
            desc: "Specify the exact amount or percentage to be deducted from the total order value",
            options: [
                {
                    key: "option1",
                    label: 'Cart',
                    value: 'option1'
                },
                {
                    key: "option2",
                    label: 'Checkout',
                    value: 'option2'
                },
                {
                    key: "option3",
                    label: 'Shop',
                    value: 'option3'
                },
            ],
            select_deselect: true,
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "Textarea",
        },
        {
            key: "sample_textarea",
            type: "textarea",
            label: 'Sample Textarea Box', 
            desc: "This is a simple textarea box",
            placeholder: "write something",
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "ToggleSetting",
        },
        {
            key: 'sample_toggle_settings',
            type: 'settingToggle',
            label: 'Sample Toggle settings',
            desc: "This is a simple toggle settings input",
            options: [
                {
                    key: "option1",
                    label: 'Popup',
                    value: 'option1'
                },
                {
                    key: "option2",
                    label: 'Inline',
                    value: 'option2'
                },
            ]
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "BlockText",
        },
        {
            key:  'sample_blocktext',
            type:  'blocktext',
            label: 'no_label',
            blocktext: "This is a simple blocktext input field",
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "Label",
        },
        {
            key:  'sample_label',
            type:  'label',
            valuename: 'sample label',
            desc: "This is a simple label input field",
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "Section",
        },
        {
            key: 'separator_content',
            type: 'section',
            label: "",
        },
        {
            key: 'separator_content',
            type: 'section',
            desc: "WpEditor",
        },
        {
            key: 'sample_wpeditor',
            type: 'wpeditor',
            label:'Sample wpeditor',
        },
    ]
};
