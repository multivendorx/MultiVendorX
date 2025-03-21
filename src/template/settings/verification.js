import { __ } from '@wordpress/i18n';

export default {
    id: 'settings-identity-verification',
    priority: 16,
    name: __( 'Verification', 'multivendorx' ),
    desc: __( 'Enable various forms of identity verification for vendors and ensure a trusted marketplace.', 'multivendorx' ),
    icon: 'adminLib-clock2',
    submitUrl: 'settings',
    modal: [
        {
            key: 'badge_img',
            type: 'file',
            label: __( 'Verified badge', 'multivendorx' ),
            width:75,
            height:75,
            desc: __( 'Upload (32px height ) size badge that will appear next to verified vendors for credibility.', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'address_verification',
            label: __( 'Address Verification', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Enable vendors to verify their physical address to enhance trust in the marketplace.', 'multivendorx' ),
            options: [
                {
                    key: 'address_verification',
                    value: 'address_verification'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'id_verification',
            label: __( 'Identity Verification', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Require vendors to verify their identity to increase marketplace security and legitimacy.', 'multivendorx' ),
            options: [
                {
                    key: 'id_verification',
                    value: 'id_verification'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'admin_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( 'Verification-only access', 'multivendorx' ),
        },
        {
            key: 'endpoint_control',
            label: __( 'Restrict access to other pages', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Allow unverified vendors to access only the verification page. Once they complete the verification process, they can access the rest of the dashboard.', 'multivendorx' ),
            options: [
                {
                    key: 'endpoint_control',
                    value: 'endpoint_control'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'redirect_verification_page',
            label: __( 'Redirect to verification page', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Automatically redirect unverified vendors to the verification page.', 'multivendorx' ),
            options: [
                {
                    key: 'redirect_verification_page',
                    value: 'redirect_verification_page'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'disable_add_product_endpoint',
            label: __( 'Disable add product', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Block vendors from adding products or accessing certain sections until verified.  ', 'multivendorx' ),
            options: [
                {
                    key: 'disable_add_product_endpoint',
                    value: 'disable_add_product_endpoint'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'admin_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( 'Google', 'multivendorx' ),
        },
        {
            key: 'google_enable',
            label: __( 'Enable', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Enable this social verification for vendor', 'multivendorx' ),
            options: [
                {
                    key: 'google_enable',
                    value: 'google_enable'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'google_redirect_url',
            type: 'text',
            label:__( 'Redirect URI', 'multivendorx' ),
            desc: __( 'User redirect URL after successfully authenticated.', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'google_client_id',
            type: 'text',
            label:__( 'Client ID', 'multivendorx' ),
            desc: __( '', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'google_client_secret',
            type: 'text',
            label:__( 'Client Secret', 'multivendorx' ),
            desc: __( `<br>**<a target='_blank' href='https://console.developers.google.com/project'>${ __( 'Create an App', 'multivendorx' )}</a>${__( ' to get your above Client ID and Client Secret.', 'multivendorx' )}` ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'admin_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( 'Facebook', 'multivendorx' ),
        },
        {
            key: 'facebook_enable',
            label: __( 'Enable', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Enable this social verification for vendor', 'multivendorx' ),
            options: [
                {
                    key: 'facebook_enable',
                    value: 'facebook_enable'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'facebook_redirect_url',
            type: 'text',
            label:__( 'Redirect URI', 'multivendorx' ),
            desc: __( 'User redirect URL after successfully authenticated.', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'facebook_client_id',
            type: 'text',
            label:__( 'App ID', 'multivendorx' ),
            desc: __( '', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'facebook_client_secret',
            type: 'text',
            label:__( 'App Secret', 'multivendorx' ),
            desc: __( `<br>**<a target='_blank' href='https://developers.facebook.com/apps/'>${__( 'Create an App', 'multivendorx' )}</a>${__( ' to get your above App ID and App Secret.', 'multivendorx' )}` ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'admin_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( 'Twitter', 'multivendorx' ),
        },
        {
            key: 'twitter_enable',
            label: __( 'Enable', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Enable this social verification for vendor', 'multivendorx' ),
            options: [
                {
                    key: 'twitter_enable',
                    value: 'twitter_enable'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'twitter_redirect_url',
            type: 'text',
            label:__( 'Redirect URI', 'multivendorx' ),
            desc: __( 'User redirect URL after successfully authenticated.', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'twitter_client_id',
            type: 'text',
            label:__( 'Consumer Key', 'multivendorx' ),
            desc: __( '', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'twitter_client_secret',
            type: 'text',
            label:__( 'Consumer Secret', 'multivendorx' ),
            desc: __( `<br>**<a target='_blank' href='https://apps.twitter.com/'>${__( 'Create an App', 'multivendorx' )}</a>${__( ' to get your above Consumer Key and Consumer Secret.', 'multivendorx' )}` ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'separator_content',
            type: 'section',
        },
        {
            key:  'admin_template_settings',
            type:  'blocktext',
            label: __( 'no_label', 'multivendorx' ),
            blocktext: __( 'Linkedin', 'multivendorx' ),
        },
        {
            key: 'linkedin_enable',
            label: __( 'Enable', 'multivendorx' ),
            type: 'checkbox',
            desc: __( 'Enable this social verification for vendor', 'multivendorx' ),
            options: [
                {
                    key: 'linkedin_enable',
                    value: 'linkedin_enable'
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'linkedin_redirect_url',
            type: 'text',
            label:__( 'Redirect URI', 'multivendorx' ),
            desc: __( 'User redirect URL after successfully authenticated.', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'linkedin_client_id',
            type: 'text',
            label:__( 'Client ID', 'multivendorx' ),
            desc: __( '', 'multivendorx' ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: 'linkedin_client_secret',
            type: 'text',
            label:__( 'Client Secret', 'multivendorx' ),
            desc: __( `<br>**<a target='_blank' href='https://www.linkedin.com/developer/apps'>${__( 'Create an App', 'multivendorx' )}</a>${__( ' to get your above Client ID and Client Secret.', 'multivendorx' )}` ),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
    ]
}