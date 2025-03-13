import { __ } from '@wordpress/i18n';

export default {
    id: 'policy',
    priority: 23,
    name: __('Policy', 'mvx-pro'),
    desc: __('Add policies that are applicable to your site.', 'mvx-pro'),
    icon: 'adminLib-support',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: 'store-policy',
            type: 'textarea',
            desc: __("Site will reflect admin created policy. However vendors can edit and override store policies.", "multivendorx"),
            label: __("Store Policy", "multivendorx"),
            moduleEnabled: 'store-policy',
        },
        {
            key: 'shipping_policy',
            type: 'textarea',
            desc: __("Site will reflect admin created policy. However vendors can edit and override store policies.", "multivendorx"),
            label: __("Shipping Policy", "multivendorx"),
            moduleEnabled: 'store-policy',
        },
        {
            key: 'refund_policy',
            type: 'textarea',
            desc: __("Site will reflect admin created policy. However vendors can edit and override store policies.", "multivendorx"),
            label: __("Refund Policy", "multivendorx"),
            moduleEnabled: 'store-policy',
        },
        {
            key: 'cancellation_policy',
            type: 'textarea',
            desc: __("Site will reflect admin created policy. However vendors can edit and override store policies.", "multivendorx"),
            label: __("Cancellation / Return / Exchange Policy", "multivendorx"),
            moduleEnabled: 'store-policy',
        }
    ]
}