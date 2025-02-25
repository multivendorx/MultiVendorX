export default {
    id: 'exclusion',
    priority: 6,
    name: "Exclusion",
    desc: "Exclude catalog viewing, inquiries, and quotes by user roles and product attributes.",
    icon: 'adminLib-settings',
    submitUrl: 'save-settings',
    modal: [
        {
            key: 'exclusion',
            type: 'multi-checkbox-table',
            label: "",
            desc: "Grid Table",
            classes: 'gridTable no-label',
            rows: [
                {
                    key: "userroles_list",
                    label: 'User Role', 
                    options: appLocalizer.role_array
                },
                {
                    key: "user_list",
                    label: 'User Name',
                    options: appLocalizer.all_users
                },
                {
                    key: "product_list",
                    label: 'Product',
                    options: appLocalizer.all_products
                },
                {
                    key: "category_list",
                    label: 'Category',
                    options: appLocalizer.all_product_cat
                },
                {
                    key: "tag_list",
                    label: 'Tag',
                    options: appLocalizer.all_product_tag
                }
            ],
            columns: [
                {
                    key: "catalog_exclusion",
                    label: "Catalog", 
                },
                {
                    key: "enquiry_exclusion",
                    label: "Enquiry", 
                },
                {
                    key: "quote_exclusion",
                    label: "Quote", 
                }
            ],
        }
    ]
}