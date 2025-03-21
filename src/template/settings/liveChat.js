import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-live-chat',
    priority: 23,
    name: __( 'Live Chat', 'multivendorx' ),
    desc: __( 'Live Chat', 'multivendorx' ),
    icon: 'adminLib-wholesale',
    submitUrl: 'settings',
    modal: [
        {
            key: 'enable_chat',
            label: __( 'Enable Live Chat', 'multivendorx' ),
            type: 'checkbox',
            options: [
                {
                    key: 'enable_chat',
                    value: 'enable_chat'
                }
            ],
            look: 'toggle',
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: 'chat_provider',
            type: 'radio',
            label: __( 'Chat Provider', 'multivendorx' ),
            desc: __( 'Which chat provider you want to setup in your site?', 'multivendorx' ),
            options: [
                {
                    key: 'facebook',
                    label: __( 'Facebook Messenger', 'multivendorx' ),
                    value: 'facebook'
                },
                {
                    key: 'talkjs',
                    label: __( 'Talkjs', 'multivendorx' ),
                    value: 'talkjs'
                },
                {
                    key: 'whatsapp',
                    label: __( 'Whatsapp', 'multivendorx' ),
                    value: 'whatsapp'
                }
            ],
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: 'whatsapp_opening_pattern',
            type: 'select',
            label: __( 'Opening Pattern', 'multivendorx' ),
            options: [
                {
                    key: 'browser',
                    label: __( 'Browser', 'multivendorx' ),
                    value: 'browser',
                },
                {
                    key: 'app',
                    label: __( 'App', 'multivendorx' ),
                    value: 'app',
                }
            ],
            dependent:{
                key:'chat_provider',
                value:'whatsapp',
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: 'whatsapp_pre_filled',
            type: 'textarea',
            desc: __( 'Text that appears in the WhatsApp Chat window. Add variables {store_name}, {store_url} to replace with store name, store url.', 'multivendorx' ),
            label: __( 'Pre-filled Message', 'multivendorx' ),
            dependent:{
                key:'chat_provider',
                value:'whatsapp',
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: 'app_id',
            type: 'text',
            label: __( 'App ID', 'multivendorx' ),
            desc:__( 'Enter app generated app id here.', 'multivendorx' ),
            dependent:{
                key:'chat_provider',
                value:'talkjs',
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: 'app_secret',
            type: 'text',
            label: __( 'App Secret', 'multivendorx' ),
            desc: __( `<br>** <a target='_blank' href='https://talkjs.com/dashboard'>${__( 'Click here', 'multivendorx' )}</a>${__( ' to get your above App ID and App Secret', 'multivendorx' )}` ),
            dependent:{
                key:'chat_provider',
                value:'talkjs',
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: 'messenger_color',
            type: 'color',
            label: __( 'Messenger Color', 'multivendorx' ),
            dependent:{
                key:'chat_provider',
                value:'facebook',
            },
            moduleEnabled: 'live-chat',
            proSetting: true,
        },
        {
            key: 'product_page_chat',
            type: 'select',
            label: __( 'Chat Button on Product Page', 'multivendorx' ),
            desc: __( 'Choose your preferred place to display chat button.', 'multivendorx' ),
            options: [
                {
                    key: 'add_to_cart_button',
                    label: __( 'Add to Cart Button', 'multivendorx' ),
                    value: 'add_to_cart_button'
                },
                {
                    key: 'vendor_info',
                    label: __( 'Vendor Details Tab', 'multivendorx' ),
                    value: 'vendor_info'
                },
                {
                    key: 'none',
                    label: __( 'Hide', 'multivendorx' ),
                    value: 'none'
                }
            ],
            moduleEnabled: 'live-chat',
            proSetting: true,
        }
    ]
}