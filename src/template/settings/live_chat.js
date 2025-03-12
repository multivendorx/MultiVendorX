import { __ } from '@wordpress/i18n';

export default {
    id: 'live_chat',
    priority: 17,
    name: __('Live Chat', 'mvx-pro'),
    desc: __('Live Chat', 'mvx-pro'),
    icon: 'adminLib-wholesale',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "enable_chat",
            label: __( 'Enable Live Chat', 'mvx-pro' ),
            type: "checkbox",
            options: [
                {
                    key: "enable_chat",
                    value: "enable_chat"
                }
            ],
            look: "toggle",
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: "chat_provider",
            type: "radio",
            label: __("Chat Provider", "mvx-pro"),
            desc: __("Which chat provider you want to setup in your site?", "mvx-pro"),
            options: [
                {
                    key: "facebook",
                    label: __("Facebook Messenger", "mvx-pro"),
                    value: "facebook"
                },
                {
                    key: "talkjs",
                    label: __("Talkjs", "mvx-pro"),
                    value: "talkjs"
                },
                {
                    key: "whatsapp",
                    label: __("Whatsapp", "mvx-pro"),
                    value: "whatsapp"
                }
            ],
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: "whatsapp_opening_pattern",
            type: "select",
            label: __('Opening Pattern', 'mvx-pro'),
            options: [
                {
                    key: "browser",
                    label: __('Browser', 'mvx-pro'),
                    value: "browser",
                },
                {
                    key: "app",
                    label: __('App', 'mvx-pro'),
                    value: "app",
                }
            ],
            dependent:{
                key:"chat_provider",
                value:"whatsapp",
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: "whatsapp_pre_filled",
            type: "textarea",
            desc: __('Text that appears in the WhatsApp Chat window. Add variables {store_name}, {store_url} to replace with store name, store url.', 'mvx-pro'),
            label: __( 'Pre-filled Message', 'mvx-pro' ),
            dependent:{
                key:"chat_provider",
                value:"whatsapp",
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: "app_id",
            type: "text",
            label: __( 'App ID', 'mvx-pro' ),
            desc:__('Enter app generated app id here.','mvx-pro'),
            dependent:{
                key:"chat_provider",
                value:"talkjs",
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: "app_secret",
            type: "text",
            label: __( 'App Secret', 'mvx-pro' ),
            desc: __(`<br>** <a target="_blank" href="https://talkjs.com/dashboard">${__( 'Click here', 'mvx-pro' )}</a>${__(' to get your above App ID and App Secret','mvx-pro')}`),
            dependent:{
                key:"chat_provider",
                value:"talkjs",
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: "messenger_color",
            type: "color",
            label: __( 'Messenger Color', 'mvx-pro' ),
            dependent:{
                key:"chat_provider",
                value:"facebook",
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: "product_page_chat",
            type: "select",
            label: __( 'Chat Button on Product Page', 'mvx-pro' ),
            desc: __( 'Choose your preferred place to display chat button.', 'mvx-pro' ),
            options: [
                {
                    key: "add_to_cart_button",
                    label: __('Add to Cart Button', 'mvx-pro'),
                    value: "add_to_cart_button"
                },
                {
                    key: "vendor_info",
                    label: __('Vendor Details Tab', 'mvx-pro'),
                    value: "vendor_info"
                },
                {
                    key: "none",
                    label: __('Hide', 'mvx-pro'),
                    value: "none"
                }
            ],
            moduleEnabled: 'live-chat',
            proSetting: true,
        }
    ]
}