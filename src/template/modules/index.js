import { __ } from '@wordpress/i18n';

export default [
    {
        parent_category:__( 'Marketplace Types', 'multivendorx' ),
        hint:"",
        modules : [
            {
                id: 'simple',
                name: __( 'Simple (Downloadable & Virtual)', 'multivendorx' ),
                desc: "<fieldset><legend>Free</legend><span>Covers the vast majority of any tangible products you may sell or ship i.e books.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/simple-product',
                settings_link: '',
                required_plugin_list:[],
                pro_module: false,
            },
            {
                id: 'variable',
                name: __( 'Variable', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>A product with variations, like different SKU, price, stock option, etc.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/variable-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/pricing',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'external',
                name: __( 'External', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Grants vendor the option to list and describe on admin website but sold elsewhere.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/external-product/',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/pricing',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'grouped',
                name: __( 'Grouped', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>A cluster of simple related products that can be purchased individually.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/grouped-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'booking',
                name: __( 'Booking', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Allow customers to book appointments, make reservations or rent equipment etc.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/booking-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('WooCommerce Booking', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/docs/knowledgebase/appointment-product/',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'appointment',
                name: __( 'Appointments', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Allow customers to book appointments.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/appointment-product/',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('WooCommerce Appointment', 'multivendorx'),
                        plugin_link  : 'https://bookingwp.com/plugins/woocommerce-appointments/',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'subscription',
                name: __( 'Subscription', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Let customers subscribe to your products or services and pay weekly, monthly or yearly.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/subscription-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('WooCommerce Subscription', 'multivendorx'),
                        plugin_link  : 'https://woocommerce.com/products/woocommerce-subscriptions/',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/pricing',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'accommodation',
                name: __( 'Accommodation', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Grant your guests the ability to quickly book overnight stays in a few clicks.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/accommodation-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('WooCommerce Accommodation & Booking', 'multivendorx'),
                        plugin_link  : 'https://woocommerce.com/products/woocommerce-accommodation-bookings/',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('WooCommerce Booking', 'multivendorx'),
                        plugin_link  : 'https://woocommerce.com/products/woocommerce-bookings/',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'bundle',
                name: __( 'Bundle', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Offer personalized product bundles, bulk discount packages, and assembled products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/bundle-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('Product Bundle', 'multivendorx'),
                        plugin_link  : 'https://woocommerce.com/products/product-bundles/',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'auction',
                name: __( 'Auction', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Implement an auction system similar to eBay on your store.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/auction-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('Simple Auction', 'multivendorx'),
                        plugin_link  : '',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'rental-pro',
                name: __( 'Rental Pro', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Perfect for those desiring to offer rental, booking, or real estate agencies or services.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowladgebase/rental-product',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('Rental Pro', 'multivendorx'),
                        plugin_link  : 'https://codecanyon.net/item/rnb-woocommerce-rental-booking-system/14835145?ref=redqteam',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'gift-card',
                name: __( 'Gift Cards', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Activate this module to offer gift cards, boosting your store's earnings and attracting fresh clientele.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/gift-card/',
                settings_link: '',
                required_plugin_list:[
                    {
                        plugin_name  : __('YITH WooCommerce Gift Cards', 'multivendorx'),
                        plugin_link  : 'https://wordpress.org/plugins/yith-woocommerce-gift-cards/',
                        is_active    :  false,
                    },
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
        ]
    },
    {
        parent_category:__('Seller management ', 'multivendorx'),
        hint:"",
        modules :[
            {
                id: 'identity-verification',
                name: __( 'Seller Identity Verification', 'multivendorx' ),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Verify vendors on the basis of Id documents, Address  and Social Media Account.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/identity-verification/',
                settings_link: appLocalizer.identity_verification_settings_url,
                required_plugin_list:[
                    {
                        plugin_name  : __('MultivendorX Pro', 'multivendorx'),
                        plugin_link  : 'https://multivendorx.com/',
                        is_active    :  false,
                    }
                ],
                pro_module: true
            },
        ]
    },
    {
        parent_category: __('Product management', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'spmv',
                name: __('Single Product Multiple Vendor', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Lets multiple vendors sell the same products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/single-product-multiple-vendors-spmv',
                required_plugin_list: [],
                settings_link: appLocalizer.spmv_settings_url,
                pro_module: false
            },
            {
                id: 'import-export',
                name: __('Import Export', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Helps vendors seamlessly import or export product data using CSV etc.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/import-export',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'store-inventory',
                name: __('Store Inventory', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Present vendors with the choice to handle normal product quantities, set low inventory and no inventory alarms and manage a subscriber list for the unavailable products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/store-inventory',
                settings_link: appLocalizer.store_inventory_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'min-max',
                name: __('Min Max Quantities', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Set a minimum or maximum purchase quantity or amount for the products of your marketplace.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/non-knowledgebase/min-max-quantities/',
                required_plugin_list: [],
                settings_link: appLocalizer.min_max_settings_url,
                pro_module: false
            }
        ]
    },
    {
        parent_category: __('Payment', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'bank-payment',
                name: __('Bank Transfer', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Manually transfer money directly to the vendor's bank account.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/direct-bank-transfer/',
                settings_link: '',
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'paypal-masspay',
                name: __('PayPal Masspay', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Schedule payment to multiple vendors at the same time.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/paypal-masspay/',
                settings_link: '',
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'paypal-payout',
                name: __('PayPal Payout', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Send payments automatically to multiple vendors as per schedule.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/paypal-payout',
                settings_link: '',
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'paypal-marketplace',
                name: __('PayPal Marketplace (Real time Split)', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Using split payment, pay vendors instantly after a completed order.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/paypal-marketplace-real-time-split/',
                settings_link: appLocalizer.wc_mvx_paypal_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'stripe-connect',
                name: __('Stripe Connect', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Connect to vendors' Stripe accounts and make hassle-free transfers as scheduled.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/stripe-connect',
                required_plugin_list: [],
                settings_link: '',
                pro_module: false
            },
            {
                id: 'stripe-marketplace',
                name: __('Stripe Marketplace (Real time Split)', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Real-Time Split payments pay vendors directly after a completed order.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/stripe-marketplace',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'mangopay',
                name: __('Mangopay', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Gives the benefit of both real-time split transfer and scheduled distribution.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/mangopay',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false,
                    }
                ],
                pro_module: true
            },
            {
                id: 'razorpay',
                name: __('Razorpay', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>For clients looking to pay multiple Indian vendors instantly.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/payment/',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MVX Razorpay Split Payment', 'multivendorx'),
                        plugin_link: 'https://wordpress.org/plugins/mvx-razorpay-split-payment/',
                        is_active: false
                    }
                ],
                pro_module: false
            }
        ]
    },
    {
        parent_category: __('Shipping', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'zone-shipping',
                name: __('Zone-Wise Shipping', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Limit vendors to sell in selected zones.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/shipping-by-zone/',
                settings_link: appLocalizer.wc_shipping_settings_url,
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'distance-shipping',
                name: __('Distance Shipping', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Calculate rates based on distance between the vendor store and drop location.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/distance-shipping',
                settings_link: appLocalizer.wc_shipping_by_distance_settings_url,
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'country-shipping',
                name: __('Country-Wise Shipping', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Let vendors choose and manage shipping to countries of their choice.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/country-shipping',
                settings_link: appLocalizer.wc_shipping_by_country_settings_url,
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'weight-shipping',
                name: __('Weight Wise Shipping (using Table Rate Shipping)', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Vendors can create shipping rates based on price, weight, and quantity.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/weight-shipping',
                settings_link: appLocalizer.wc_shipping_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('Table Rate Shipping', 'multivendorx'),
                        plugin_link: 'https://woocommerce.com/products/table-rate-shipping/',
                        is_active: false
                    }
                ],
                pro_module: false
            },
            {
                id: 'per-product-shipping',
                name: __('Per Product Shipping', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Let vendors add shipping costs to specific products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/per-product-shipping',
                settings_link: appLocalizer.wc_shipping_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('Per Product Shipping', 'multivendorx'),
                        plugin_link: 'https://woocommerce.com/products/per-product-shipping/',
                        is_active: false
                    },
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            }
        ]
    },
    {
        parent_category: __('Order Management', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'invoice',
                name: __('Invoice & Packing slip', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Send invoice and packaging slips to vendor.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/invoice-packing-slip',
                settings_link: appLocalizer.vendor_invoice_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'marketplace-refund',
                name: __('Marketplace Refund', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Enable customer refund requests & let vendors manage customer refunds.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/marketplace-refund',
                settings_link: appLocalizer.marketplace_refunds_settings_url,
                required_plugin_list: [],
                pro_module: false
            }
        ]
    },
    {
        parent_category: __('Store Management', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'store-location',
                name: __('Store Location', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>If enabled, customers can view a vendor's store location.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/store-location',
                settings_link: appLocalizer.store_settings_url,
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'store-policy',
                name: __('Store Policy', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Offers vendors the option to set individual store-specific policies.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/store-policy',
                settings_link: appLocalizer.policy_settings_url,
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'follow-store',
                name: __('Follow Store', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Permit customers to follow stores, receive updates & let vendors keep track of customers.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/follow-store',
                settings_link: '',
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'store-review',
                name: __('Store Review', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Allows customers to rate and review stores and their purchased products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/store-review',
                settings_link: appLocalizer.review_management_settings_url,
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'business-hours',
                name: __('Business Hours', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Gives vendors the option to set and manage business timings.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/business-hours/',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'mvx-blocks',
                name: __('Gutenberg Blocks', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Lets you add widgets using the Gutenberg block editor. Use the block to register our widget area on any page or post using the Gutenberg editor.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: '',
                settings_link: '',
                required_plugin_list: [],
                pro_module: false
            },
            {
                id: 'advertisement',
                name: __('Advertise Product', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Enable the option of paid advertisement by letting vendors advertise their products on your website.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/advertise-product/',
                settings_link: appLocalizer.product_advertising_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            }
        ]
    },
    {
        parent_category: __('Store Component', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'vacation',
                name: __('Vacation', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>On vacation mode, vendor can allow/disable sale & notify customers accordingly.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/vacation',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'staff-manager',
                name: __('Staff Manager', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Lets vendors hire and manage staff to support the store.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/staff-manager',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'wholesale',
                name: __('Wholesale', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Set wholesale price and quantity for customers.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/wholesale',
                settings_link: appLocalizer.wholesale_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'live-chat',
                name: __('Live Chat', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Allows real-time messaging between vendors and customers.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/live-chat',
                settings_link: appLocalizer.live_chat_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'store-support',
                name: __('Store Support', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Streamline order support with a vendor-customer ticketing system.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/store-support/',
                settings_link: appLocalizer.store_support_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            }
        ]
    },
    {
        parent_category: __('Analytics', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'store-analytics',
                name: __('Store Analytics', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Gives vendors detailed store reports & connects to Google Analytics.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/store-analytics',
                settings_link: '',//add later
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'store-seo',
                name: __('Store SEO', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Lets vendors manage their store SEO using Rank Math and Yoast SEO.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/store-seo',
                settings_link: appLocalizer.seo_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            }
        ]
    },
    {
        parent_category: __('Marketplace Membership', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'marketplace-membership',
                name: __('Marketplace Membership', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Lets Admin create marketplace membership levels and manage vendor-wise individual capability.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/marketplace-memberhsip',
                settings_link: '',//added later
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            }
        ]
    },
    {
        parent_category: __('Notification', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'announcement',
                name: __('Announcement', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Lets admin broadcast important news to sellers.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/announcement/',
                settings_link: '',//added later
                pro_module: false
            },
            {
                id: 'report-abuse',
                name: __('Report Abuse', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Lets customers report false products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/report-abuse',
                settings_link: '',//added later
                pro_module: false
            },
            {
                id: 'knowladgebase',
                name: __('Knowledgebase', 'multivendorx'),
                desc: "<fieldset><legend>Free</legend><span>Admin can share tutorials and other vendor-specific information with vendors.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/knowledgebase/',
                settings_link: '',//added later
                pro_module: false
            }
        ]
    },
    {
        parent_category: __('Third Party Compatibility', 'multivendorx'),
        hint: "",
        modules: [
            {
                id: 'elementor',
                name: __('Elementor', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-free-tab'></i> Free</legend><span>Create Sellers Pages using Elementors drag and drop feature.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/mvx-elementor',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('Elementor Website Builder', 'multivendorx'),
                        plugin_link: 'https://wordpress.org/plugins/elementor/',
                        is_active: false
                    },
                    {
                        plugin_name: __('Elementor Pro', 'multivendorx'),
                        plugin_link: 'https://elementor.com/pricing/',
                        is_active: false
                    }
                ],
                pro_module: false
            },
            {
                id: 'buddypress',
                name: __('Buddypress', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-free-tab'></i> Free</legend><span>Allows stores to have a social networking feature.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/mvx-buddypress',
                settings_link: appLocalizer.social_settings_url,
                required_plugin_list: [
                    {
                        plugin_name: __('Buddypress', 'multivendorx'),
                        plugin_link: 'https://wordpress.org/plugins/buddypress/',
                        is_active: false
                    }
                ],
                pro_module: false
            },
            {
                id: 'wpml',
                name: __('WPML', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-free-tab'></i> Free</legend><span>Gives vendors the option of selling their product in different languages.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/mvx-wpml',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('The WordPress Multilingual Plugin', 'multivendorx'),
                        plugin_link: 'https://wpml.org/',
                        is_active: false
                    },
                    {
                        plugin_name: __('WooCommerce Multilingual – run WooCommerce with WPML', 'multivendorx'),
                        plugin_link: 'https://wordpress.org/plugins/woocommerce-multilingual/',
                        is_active: false
                    }
                ],
                pro_module: false
            },
            {
                id: 'advance-custom-field',
                name: __('Advance Custom field', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Allows for an on-demand product field in Add Product section.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/mvx-acf',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('Advanced custom fields', 'multivendorx'),
                        plugin_link: 'https://wordpress.org/plugins/advanced-custom-fields/',
                        is_active: false
                    },
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'geo-my-wp',
                name: __('GEOmyWP', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Offer vendors the option to attach location info along with their products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/geo-my-wp',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('Geo My WP', 'multivendorx'),
                        plugin_link: 'https://wordpress.org/plugins/geo-my-wp/',
                        is_active: false
                    },
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: false
            },
            {
                id: 'toolset-types',
                name: __('Toolset Types', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Allows admin to create custom fields and taxonomies for vendor's product fields.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: '',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('Toolset', 'multivendorx'),
                        plugin_link: 'https://toolset.com/',
                        is_active: false
                    },
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'wp-affiliate',
                name: __('WP Affiliate', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Launch an affiliate program into your marketplace.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/affiliate-product/',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('AffiliateWP', 'multivendorx'),
                        plugin_link: 'https://affiliatewp.com/',
                        is_active: false
                    },
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true 
            },
            {
                id: 'product-addon',
                name: __('Product Addon', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Offer add-ons like gift wrapping, special messages, etc., along with primary products.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/mvx-product-addon',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('Product Add-Ons', 'multivendorx'),
                        plugin_link: 'https://woocommerce.com/products/product-add-ons/',
                        is_active: false
                    },
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            },
            {
                id: 'shipstation-module',
                name: __('Shipstation', 'multivendorx'),
                desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Shipstation integration for vendors.</span></fieldset>",
                icon: 'adminLib-mail',
                doc_link: 'https://multivendorx.com/docs/knowledgebase/shipstation/',
                settings_link: '',
                required_plugin_list: [
                    {
                        plugin_name: __('MultivendorX Pro', 'multivendorx'),
                        plugin_link: 'https://multivendorx.com/',
                        is_active: false
                    }
                ],
                pro_module: true
            }
        ]
    }
]