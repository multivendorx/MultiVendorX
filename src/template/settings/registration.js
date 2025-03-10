import { __ } from '@wordpress/i18n';

export default {
    id: 'registration',
    priority: 11,
    name: "Registration Form",
    desc: __("Customize the registration form to gather personalized information from sellers when they join your marketplace.","multivendorx"),
    icon: 'adminLib-form-section',
    submitUrl: 'save-settings',
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
            desc: 'From builder',
            classes: 'catalog-customizer-wrapper',
        }
    ]
}