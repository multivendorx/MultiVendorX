import { __ } from '@wordpress/i18n';

export default {
    id: 'social',
    priority: 10,
    name:  __('Social', 'multivendorx'),
    desc: __('Enable features that allow direct engagement between sellers and customers on your platform.', 'multivendorx'),
    icon: 'adminLib-social-media-content',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "buddypress_enabled",
            label: __( 'Buddypress', 'multivendorx' ),
            type: "checkbox",
            desc: __('Allow sellers to showcase and sell their products directly from their BuddyPress profile, fostering a closer connection with their customers.', 'multivendorx'),
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