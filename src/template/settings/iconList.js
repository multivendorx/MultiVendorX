export default {
    id: 'icon-list',
    priority: 21,
    name: "Icon List",
    desc: "Drag-and-drop to create and customize single product page elements.",
    icon: 'adminLib-settings',
    // submitUrl: 'save-settings',
    modal: [
        {
            key: 'icon_list',
            type: 'icon-list', 
            classes: 'icon-list-wrapper'
        },
    ]
}