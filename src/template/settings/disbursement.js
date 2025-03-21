import { __ } from '@wordpress/i18n';

export default {
    id: 'disbursement',
    priority: 9,
    name: __( 'Disbursement', 'multivendorx' ),
    desc:  __( 'Manage payment and disbursement setting of your site.', 'multivendorx' ),
    icon: 'adminLib-price',
    submitUrl: 'settings',
    modal: [
        {
            key: "commission_include_coupon",
            label: __( 'Who will bear the Coupon Cost', 'multivendorx' ),
            type: "checkbox",
            desc: __( 'Tap to let the vendors bear the coupon discount charges of the coupons created by them', 'multivendorx' ),
            options: [
                {
                    key: "commission_include_coupon",
                    value: "commission_include_coupon"
                }
            ],
            look: 'toggle',
        },
        {
            key: "admin_coupon_excluded",
            label: __( 'Exclude Admin Created Coupon', 'multivendorx' ),
            desc:__( 'Bear the coupon discount charges of the coupons created by you', 'multivendorx' ),
            type: "checkbox",
            options: [
                {
                    key: "admin_coupon_excluded",
                    value: "admin_coupon_excluded"
                }
            ],
            look: 'toggle',
        },
        {
            key: "commission_calculation_on_tax",
            label: __( 'Commission Calculation On Tax', 'multivendorx' ),
            desc: __( '', 'multivendorx' ),
            type: "checkbox",
            options: [
                {
                    key: "commission_calculation_on_tax",
                    value: "commission_calculation_on_tax"
                }
            ],
            look: 'toggle',
        },
        {
            key: "give_tax",
            label: __( 'Tax', 'multivendorx' ),
            desc: __( 'Let vendor collect & manage tax amount', 'multivendorx' ),
            type: "checkbox",
            options: [
                {
                    key: "give_tax",
                    value: "give_tax"
                }
            ],
            look: 'toggle',
        },
        {
            key: "give_shipping",
            label: __( 'Shipping', 'multivendorx' ),
            desc: __( 'Allow sellers to collect & manage shipping charges', 'multivendorx' ),
            type: "checkbox",
            options: [
                {
                    key: "give_shipping",
                    value: "give_shipping"
                }
            ],
            look: 'toggle',
        },
        {
            key: "choose_payment_mode_automatic_disbursal",
            label: __( 'Disbursement Schedule', 'multivendorx' ),
            desc: __( 'Schedule when vendors would receive their commission', 'multivendorx' ),
            type: "checkbox",
            options: [
                {
                    key: "choose_payment_mode_automatic_disbursal",
                    value: "choose_payment_mode_automatic_disbursal"
                }
            ],
            look: 'toggle',
        },
        //
        {
            key: "payment_schedule",
            type: "radio",
            label: __( 'Set Schedule', 'multivendorx' ),
            options: [
                {
                    key: "weekly",
                    label: __( 'Weekly', 'multivendorx' ),
                    value: "weekly"
                },
                {
                    key: "daily",
                    label:__( 'Daily', 'multivendorx' ),
                    value: "daily"
                },
                {
                    key: "monthly",
                    label: __( 'Monthly', 'multivendorx' ),
                    value: "monthly"
                },
                {
                    key: "fortnightly",
                    label: __( 'Fortnightly', 'multivendorx' ),
                    value: "fortnightly"
                },
                {
                    key: "hourly",
                    label: __( 'Hourly', 'multivendorx' ),
                    value: "hourly"
                }
            ],
            dependent:{
                key:'choose_payment_mode_automatic_disbursal',
                set:true
            },
        },
        {
            key: "commission_threshold",
            type: 'number',
            label: __( 'Disbursement Threshold', 'multivendorx'  ),
            desc:  __( 'Add the minimum value required before payment is disbursed to the vendor', 'multivendorx' ),
        },      
        {
            key: "withdrawal_request",
            label: __( 'Allow Withdrawal Request', 'multivendorx' ),
            desc: __( 'Let vendors withdraw payment prior to reaching the agreed disbursement value', 'multivendorx' ),
            type: "checkbox",
            options: [
                {
                    key: "withdrawal_request",
                    value: "withdrawal_request"
                }
            ],
            look: 'toggle',
        },
        {
            key: "commission_threshold_time",
            type: 'number',
            label: __( 'Withdrawal Locking Period', 'multivendorx'  ),
            desc:  __( 'Refers to the minimum number of days required before a seller can send a withdrawal request', 'multivendorx' ),
            placeholder:__( 'in days', 'multivendorx' ),
        },
        {
            key: "commission_transfer",
            type: 'number',
            label: __( 'Withdrawal Charges', 'multivendorx'  ),
            desc: __( 'Vendors will be charged this amount per withdrawal after the quota of free withdrawals is over.', 'multivendorx' ),
        },
        {
            key: "no_of_orders",
            type: 'number',
            label: __( 'Number of Free Withdrawals', 'multivendorx'  ),
            desc: __( 'Number of free withdrawal requests.', 'multivendorx' ),
        },
        {
            key: "order_withdrawl_status",
            type: "multi-select",
            label: __( 'Available Order Status for Withdrawal', 'multivendorx'  ),
            desc: __( 'Withdrawal request would be available in case of these order statuses', 'multivendorx'  ),
            options: [
                {
                    key: "on-hold",
                    label: __( 'On hold', 'multivendorx' ),
                    value: 'on-hold'
                },
                {
                    key: "processing",
                    label: __( 'Processing', 'multivendorx' ),
                    value: 'processing'
                },
                {
                    key: "completed",
                    label:  __( 'Completed', 'multivendorx' ),
                    value: 'completed'
                },
            ],
        },
        
    ]
}