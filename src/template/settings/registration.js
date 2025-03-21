import { __ } from '@wordpress/i18n';

export default {
    id: 'new_vendor_registration_form',
    priority: 2,
    name: "Registration Form",
    desc: __("Customise personalised seller registration form for marketplace.","multivendorx"),
    icon: 'adminLib-form-section',
    submitUrl: 'settings',
    modal: [
        {
            key:  'registration page',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( "Username and Password fileds display as per WooCommerce settings", 'multivendorx' ),
        },
        {
            key: 'from_builder',
            type: 'from-builder',
            desc: 'Customise personalised seller registration form for marketplace.',
        }
    ]
}