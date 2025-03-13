import { __ } from '@wordpress/i18n';

export default {
    id: 'verification',
    priority: 13,
    name: __('Verification', 'mvx-pro'),
    desc: __("Enable various forms of identity verification for vendors and ensure a trusted marketplace.", 'mvx-pro'),
    icon: 'adminLib-clock2',
    submitUrl: 'save_dashpages',
    modal: [
        {
            key: "badge_img",
            type: "file",
            label: __( 'Verified badge', 'mvx-pro' ),
            width:75,
            height:75,
            desc: __('Upload (32px height) size badge that will appear next to verified vendors for credibility.', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "address_verification",
            label: __( 'Address Verification', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Enable vendors to verify their physical address to enhance trust in the marketplace.", 'mvx-pro'),
            options: [
                {
                    key: "address_verification",
                    value: "address_verification"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "id_verification",
            label: __( 'Identity Verification', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Require vendors to verify their identity to increase marketplace security and legitimacy.", 'mvx-pro'),
            options: [
                {
                    key: "id_verification",
                    value: "id_verification"
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
            label: __( 'no_label', 'mvx-pro' ),
            blocktext: __( "Verification-only access", 'mvx-pro' ),
        },
        {
            key: "endpoint_control",
            label: __( 'Restrict access to other pages', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Allow unverified vendors to access only the verification page. Once they complete the verification process, they can access the rest of the dashboard.", 'mvx-pro'),
            options: [
                {
                    key: "endpoint_control",
                    value: "endpoint_control"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "redirect_verification_page",
            label: __( 'Redirect to verification page', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Automatically redirect unverified vendors to the verification page.", 'mvx-pro'),
            options: [
                {
                    key: "redirect_verification_page",
                    value: "redirect_verification_page"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "disable_add_product_endpoint",
            label: __( 'Disable add product', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Block vendors from adding products or accessing certain sections until verified.  ", 'mvx-pro'),
            options: [
                {
                    key: "disable_add_product_endpoint",
                    value: "disable_add_product_endpoint"
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
            label: __( 'no_label', 'mvx-pro' ),
            blocktext: __( "Google", 'mvx-pro' ),
        },
        {
            key: "google_enable",
            label: __( 'Enable', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Enable this social verification for vendor", 'mvx-pro'),
            options: [
                {
                    key: "google_enable",
                    value: "google_enable"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "google_redirect_url",
            type: "text",
            label:__( 'Redirect URI', 'mvx-pro' ),
            desc: __('User redirect URL after successfully authenticated.', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "google_client_id",
            type: "text",
            label:__( 'Client ID', 'mvx-pro' ),
            desc: __('', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "google_client_secret",
            type: "text",
            label:__( 'Client Secret', 'mvx-pro' ),
            desc: __(`<br>**<a target="_blank" href="https://console.developers.google.com/project">${ __( 'Create an App', 'mvx-pro' )}</a>${__(' to get your above Client ID and Client Secret.', 'mvx-pro')}`),
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
            label: __( 'no_label', 'mvx-pro' ),
            blocktext: __( "Facebook", 'mvx-pro' ),
        },
        {
            key: "facebook_enable",
            label: __( 'Enable', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Enable this social verification for vendor", 'mvx-pro'),
            options: [
                {
                    key: "facebook_enable",
                    value: "facebook_enable"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "facebook_redirect_url",
            type: "text",
            label:__( 'Redirect URI', 'mvx-pro' ),
            desc: __('User redirect URL after successfully authenticated.', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "facebook_client_id",
            type: "text",
            label:__( 'App ID', 'mvx-pro' ),
            desc: __('', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "facebook_client_secret",
            type: "text",
            label:__( 'App Secret', 'mvx-pro' ),
            desc: __(`<br>**<a target="_blank" href="https://developers.facebook.com/apps/">${__( 'Create an App', 'mvx-pro' )}</a>${__(' to get your above App ID and App Secret.', 'mvx-pro')}`),
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
            label: __( 'no_label', 'mvx-pro' ),
            blocktext: __( "Twitter", 'mvx-pro' ),
        },
        {
            key: "twitter_enable",
            label: __( 'Enable', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Enable this social verification for vendor", 'mvx-pro'),
            options: [
                {
                    key: "twitter_enable",
                    value: "twitter_enable"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "twitter_redirect_url",
            type: "text",
            label:__( 'Redirect URI', 'mvx-pro' ),
            desc: __('User redirect URL after successfully authenticated.', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "twitter_client_id",
            type: "text",
            label:__( 'Consumer Key', 'mvx-pro' ),
            desc: __('', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "twitter_client_secret",
            type: "text",
            label:__( 'Consumer Secret', 'mvx-pro' ),
            desc: __(`<br>**<a target="_blank" href="https://apps.twitter.com/">${__( 'Create an App', 'mvx-pro' )}</a>${__(' to get your above Consumer Key and Consumer Secret.', 'mvx-pro')}`),
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
            label: __( 'no_label', 'mvx-pro' ),
            blocktext: __( "Linkedin", 'mvx-pro' ),
        },
        {
            key: "linkedin_enable",
            label: __( 'Enable', 'mvx-pro' ),
            type: "checkbox",
            desc: __("Enable this social verification for vendor", 'mvx-pro'),
            options: [
                {
                    key: "linkedin_enable",
                    value: "linkedin_enable"
                }
            ],
            look: 'toggle',
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "linkedin_redirect_url",
            type: "text",
            label:__( 'Redirect URI', 'mvx-pro' ),
            desc: __('User redirect URL after successfully authenticated.', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "linkedin_client_id",
            type: "text",
            label:__( 'Client ID', 'mvx-pro' ),
            desc: __('', 'mvx-pro'),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
        {
            key: "linkedin_client_secret",
            type: "text",
            label:__( 'Client Secret', 'mvx-pro' ),
            desc: __(`<br>**<a target="_blank" href="https://www.linkedin.com/developer/apps">${__( 'Create an App', 'mvx-pro' )}</a>${__(' to get your above Client ID and Client Secret.', 'mvx-pro')}`),
            proSetting:true,
            moduleEnabled: 'identity-verification',
        },
    ]
}