export default {
    id: 'free_pro_from_builder',
    priority: 5,
    name: "Free Pro From builder",
    desc: "Exclude catalog viewing, inquiries, and quotes by user roles and product attributes.",
    icon: 'adminLib-settings',
    submitUrl: 'save-settings',
    modal: [
        {
            key: 'free_pro_from_builder',
            type: 'form-customizer',
            desc: 'From customizer',
            classes: 'form_customizer',
        }
    ]
}