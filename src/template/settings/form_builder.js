export default {
    id: 'from_builder',
    priority: 4,
    name: "From builder",
    desc: "Exclude catalog viewing, inquiries, and quotes by user roles and product attributes.",
    icon: 'adminLib-settings',
    submitUrl: 'save-settings',
    modal: [
        {
            key: 'from_builder',
            type: 'from-builder',
            desc: 'From builder',
            classes: 'catalog-customizer-wrapper',
        }
    ]
}