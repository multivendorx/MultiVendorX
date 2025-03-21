import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-store-inventory',
    priority: 22,
    name: __( 'Store Inventory', 'multivendorx' ),
    desc: __( 'Store Inventory', 'multivendorx' ),
    icon: 'adminLib-support',
    submitUrl: 'settings',
    modal: [
        {
            key: 'low_stock_enabled',
            label: __( 'Enable low stock alert for Vendors', 'multivendorx' ),
            desc: __( 'It will enable low stock limit', 'multivendorx' ),
            type: 'checkbox',
            options: [
                {
                    key: 'low_stock_enabled',
                    value: 'low_stock_enabled'
                }
            ],
            look: 'toggle',
            proSetting: true,
            moduleEnabled: 'store-inventory',
        },
        {
            key: 'out_of_stock_enabled',
            label: __( 'Enable out of stock alert for Vendors', 'multivendorx' ),
            desc: __( 'It will enable out of stock limit', 'multivendorx' ),
            type: 'checkbox',
            options: [
                {
                    key: 'out_of_stock_enabled',
                    value: 'out_of_stock_enabled'
                }
            ],
            look: 'toggle',
            proSetting: true,
            moduleEnabled: 'store-inventory',
        },
        {
            key:'low_stock_limit',
            type :'text',
            label:__( 'Low stock alert limit for Vendors', 'multivendorx' ),
            desc :__( 'It will represent low stock limit', 'multivendorx' ),
            dependent: {
                key: 'low_stock_enabled',
                set:true,
            },
            moduleEnabled: 'store-inventory',
        },
        {
            key:'out_of_stock_limit',
            type :'text',
            label:__( 'Out of stock alert limit for Vendors', 'multivendorx' ),
            desc :__( 'It will represent out of stock limit', 'multivendorx' ),
            dependent: {
                key: 'out_of_stock_enabled',
                set:true,
            },
            moduleEnabled: 'store-inventory',
        },
    ]
}