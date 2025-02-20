export default {
    id: 'catalog_customizer',
    priority: 3,
    name: "Catalog customizer",
    desc: "Design a personalized enquiry form with built-in form builder. ",
    icon: 'adminLib-storefront',
    submitUrl: 'save-settings',
    modal: [
        {
            key: 'catalog_customizer',
            type: 'catalog-customizer',
            desc: 'Catalog Customizer',
            classes: 'catalog-customizer-wrapper',
        }
    ]
}