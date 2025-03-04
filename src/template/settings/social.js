import { __ } from '@wordpress/i18n';

export default {
    id: 'social',
    priority: 10,
    name:  __('Social', 'multivendorx'),
    desc: __('Create a platform for seller-customer interaction.', 'multivendorx'),
    icon: 'adminLib-social-media-content',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "buddypress_enabled",
            label: __( 'Buddypress', 'multivendorx' ),
            type: "checkbox",
            desc: __('Allows sellers to sell products on their BuddyPress profile while connecting with their customers', 'multivendorx'),
            options: [
                {
                    key: "buddypress_enabled",
                    value: "buddypress_enabled"
                }
            ],
            look: 'toggle',
        }
        
    ]
}