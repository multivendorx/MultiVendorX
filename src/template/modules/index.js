import { __ } from '@wordpress/i18n';

export default [
    {
        id: 'demo1',
        name: 'Demo1',
        desc: "<fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Idea for showcasing products by hiding prices, disabling purchases, and restricting cart/checkout access.</span></fieldset>",
        icon: 'adminLib-mail',
        doc_link: '',
        settings_link: '',
        pro_module: true,
    },
    {
        id: 'demo2',
        name: 'Demo2',
        desc: "<fieldset><legend>Free</legend><span> Add inquiry button for single product email enquiries to admin.</span></fieldset><fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span> Full messaging hub with two-way communication, multi-product ennquiries, and centralized management.</span></fieldset>",
        icon: 'adminLib-mail',
        doc_link: '',
        settings_link: '',
        pro_module: false
    },
    {
        id: 'demo3',
        name: "Demo3",
        desc: "<fieldset><legend>Free</legend><span>Add quotation button for customers to request product quotes via email.</span></fieldset><fieldset><legend><i class='adminLib-pro-tab'></i> Pro</legend><span>Manage quotations with dedicated list views, generate and monitor quotes from admin panel, offer PDF downloads, and set expiry dates.</span></fieldset>",
        icon: 'adminLib-mail',
        doc_link: '',
        settings_link: '',
        pro_module: false
    },
]