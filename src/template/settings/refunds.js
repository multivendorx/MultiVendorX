import { __ } from '@wordpress/i18n';

export default {
    id: 'refund-management',
    priority: 12,
    name: __( 'Refunds', 'multivendorx' ),
    desc: __( 'Set conditions for refund requests.', 'multivendorx' ),
    icon: 'adminLib-form-section',
    submitUrl: 'settings',
    modal: [
        {
            key: 'customer_refund_status',
            type: 'checkbox',
            label: __( 'Available Status for Refund', 'multivendorx' ),
            class: 'mvx-toggle-checkbox',
            desc:  __( 'Customers would be able to avail a refund only if their order is at the following stage/s', 'multivendorx' ),
            options: [
                {
                    key: 'pending',
                    label: __( 'Pending', 'multivendorx' ),
                    value: 'pending',
                },
                {
                    key: 'on-hold',
                    label: __( 'On hold', 'multivendorx' ),
                    value: 'on-hold',
                },
                {
                    key: 'processing',
                    label: __( 'Processing', 'multivendorx' ),
                    value: 'processing',
                },
                {
                    key: 'completed',
                    label: __( 'Completed', 'multivendorx' ),
                    value: 'completed',
                }
            ],
            select_deselect: true,
            moduleEnabled: 'marketplace-refund',
        },
        {
            key :'refund_days',
            type:'number',
            label:__( 'Refund Claim Period (In Days )', 'multivendorx' ),
            desc:  __( 'The duration till which the refund request is available/valid', 'multivendorx' ),
            max:365,
            moduleEnabled: 'marketplace-refund',
        },
        {
            key: 'refund_order_msg',
            type: 'textarea',
            desc: __( 'Add reasons for a refund. Use || to separate each reason. Options will appear as a radio button to customers.', 'multivendorx' ),
            label: __( 'Reasons For Refund', 'multivendorx' ),
            moduleEnabled: 'marketplace-refund',
        },
        {
            key: 'disable_refund_customer_end',
            type: 'checkbox',
            label: __( 'Disable refund request for customer', 'multivendorx' ),
            desc: __( 'Remove capability to customer from refund request.', 'multivendorx' ),
            options: [
                {
                    key: 'disable_refund_customer_end',
                    value: 'disable_refund_customer_end'
                }
            ],
            look: 'toggle',
            moduleEnabled: 'marketplace-refund',
        },
    ]
}