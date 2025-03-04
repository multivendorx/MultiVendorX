import { __ } from '@wordpress/i18n';

export default {
    id: 'commissions',
    priority: 7,
    name: __('Commission', 'multivendorx'),
    desc:  __("Configure commission settings to customise your commission plan.", 'multivendorx'),
    icon: 'adminLib-dynamic-pricing',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: 'revenue_sharing_mode',
            type: 'settingToggle',
            label: 'Revenue Sharing Mode',
            desc: 'Select how you want the commission to be split. If you are not sure about how to setup commissions and payment options in your marketplace, kindly read this <a href="https://multivendorx.com/doc/knowladgebase/payments/" target="_blank">article</a> before proceeding.',
            options: [
                {
                    key: 'revenue_sharing_mode_admin',
                    label: 'Admin fees',
                    value: 'revenue_sharing_mode_admin'
                },
                {
                    key: 'revenue_sharing_mode_vendor',
                    label: 'Vendor commissions',
                    value: 'revenue_sharing_mode_vendor'
                }
            ],
        },
        {
            key: "commission_type",
            type: "select",
            label: __('Commission Type', 'multivendorx'),
            desc: __('Choose the Commission Option prefered by you. For better undrestanding read doc', 'multivendorx'),
            options: [
                {
                    key: "choose_commission_type",
                    label: __('Choose Commission Type', 'multivendorx'),
                    value: __('choose_commission_type', 'multivendorx'),
                },
                {
                    key: "fixed",
                    label: __('Fixed Amount', 'multivendorx'),
                    value: __('fixed', 'multivendorx'),
                },
                {
                    key: "percent",
                    label: __('Percentage', 'multivendorx'),
                    value: __('percent', 'multivendorx'),
                },
                {
                    key: "fixed_with_percentage",
                    label: __('%age + Fixed (per transaction)', 'multivendorx'),
                    value: __('fixed_with_percentage', 'multivendorx'),
                },
                {
                    key: "fixed_with_percentage_qty",
                    label: __('%age + Fixed (per unit)', 'multivendorx'),
                    value: __('fixed_with_percentage_qty', 'multivendorx'),
                },
                {
                    key: "commission_by_product_price",
                    label: __('Commission By Product Price', 'multivendorx'),
                    value: __('commission_by_product_price', 'multivendorx'),
                },
                {
                    key: "commission_by_purchase_quantity",
                    label: __('Commission By Purchase Quantity', 'multivendorx'),
                    value: __('commission_by_purchase_quantity', 'multivendorx'),
                },
                {
                    key: "fixed_with_percentage_per_vendor",
                    label: __('%age + Fixed (per vendor)', 'multivendorx'),
                    value: __('fixed_with_percentage_per_vendor', 'multivendorx'),
                },
                {
                    key: "commission_calculation_on_tax",
                    label: __('Commission Calculation on Tax', 'multivendorx'),
                    value: __('commission_calculation_on_tax', 'multivendorx'),
                },
            ],
        },
        // Nested Input fields added later

        {
            key: "payment_method_disbursement",
            label: __('Commission Disbursement Method', 'multivendorx'),
            desc: __(`Kindly activate your preferred payment method in the <a href="${appLocalizer.modules_page_url}">Module section</a>`, 'multivendorx'),
            type: "checkbox",
            right_content: true,
            options: [],
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key: "payment_gateway_charge",
            label: __('Payment Gateway Charge', 'multivendorx'),
            desc: __("Add the payment gateway charges incurred while paying online.", 'multivendorx'),
            type: "checkbox",
            options: [
                {
                    key: "payment_gateway_charge",
                    label: __('', 'multivendorx'),
                    value: "payment_gateway_charge"
                }
            ],
            look: 'toggle',
        },
        {
            key: "gateway_charges_cost_carrier",
            type: "select",
            label: __('Who bear the gateway charges', 'multivendorx'),
            desc: __('When it comes to automated payments, you can decide who will be responsible for the gateway charges.', 'multivendorx'),
            options: [
                {
                    key: "vendor",
                    label: __('Vendor', 'multivendorx'),
                    value: __('vendor', 'multivendorx'),
                },
                {
                    key: "admin",
                    label: __('Site owner', 'multivendorx'),
                    value: __('admin', 'multivendorx'),
                },
                {
                    key: "separate",
                    label: __('Separately', 'multivendorx'),
                    value: __('separate', 'multivendorx'),
                }
            ],
            dependent: {
                key: "payment_gateway_charge",
                set: true
            },
        },
        {
            key: "payment_gateway_charge_type",
            type: "select",
            label: __('Gateway Charge Type', 'multivendorx'),
            desc: __('Choose your preferred gateway charge type.', 'multivendorx'),
            options: [
                {
                    key: "percent",
                    label: __('Percentage', 'multivendorx'),
                    value: "percent",
                },
                {
                    key: "fixed",
                    label: __('Fixed Amount', 'multivendorx'),
                    value: "fixed",
                },
                {
                    key: "fixed_with_percentage",
                    label: __('%age + Fixed', 'multivendorx'),
                    value: "fixed_with_percentage",
                }
            ],
            dependent: {
                key: "payment_gateway_charge",
                set: true
            },
        },
        // gayeway charge value
        {
            key:'default_gateway_charge_value',
            type :'multi-number',
            label:__( 'Gateway Value', 'multivendorx' ),
            desc :__('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'multivendorx'),
            options :[
                {
                    key :'fixed_gayeway_amount_paypal_masspay',
                    type:'number',
                    label:__('Fixed paypal masspay amount', 'multivendorx'),
                    value:'fixed_gayeway_amount_paypal_masspay'
                },
            ],
            dependent: {
                key: "payment_gateway_charge_type",
                value:'fixed',
            },
            moduleEnabled: 'paypal-masspay',
        },
        {
            key:'default_gateway_charge_value',
            type :'multi-number',
            label:__( 'Gateway Value', 'multivendorx' ),
            desc :__('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'multivendorx'),
            options :[
                {
                    key :'percent_gayeway_amount_paypal_masspay',
                    type:'number',
                    label:__('Percent paypal masspay amount', 'multivendorx'),
                    value:'percent_gayeway_amount_paypal_masspay'
                },
            ],
            dependent: {
                key: "payment_gateway_charge_type",
                value:'percent',
            },
            moduleEnabled: 'paypal-masspay',
        },
        {
            key:'default_gateway_charge_value',
            type :'multi-number',
            label:__( 'Gateway Value', 'multivendorx' ),
            desc :__('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'multivendorx'),
            options :[
                {
                    key :'fixed_gayeway_amount_paypal_masspay',
                    type:'number',
                    label:__('Fixed paypal masspay amount', 'multivendorx'),
                    value:'fixed_gayeway_amount_paypal_masspay'
                },
                {
                    key :'percent_gayeway_amount_paypal_masspay',
                    type:'number',
                    label:__('Percent paypal masspay amount', 'multivendorx'),
                    value:'percent_gayeway_amount_paypal_masspay'
                },
            ],
            dependent: {
                key: "payment_gateway_charge_type",
                value:'fixed_with_percentage',
            },
            moduleEnabled: 'paypal-masspay',
        },
        
    ]
}