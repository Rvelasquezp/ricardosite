// Creating new styles for blocks
// wp.domReady(() => {
//     wp.blocks.registerBlockStyle("core/button", [
//         {
//             name: "underline",
//             label: "Underline",
//         },
//     ]);
//     wp.blocks.registerBlockStyle("core/image", [
//         {
//             name: "border",
//             label: "Border",
//         },
//     ]);
//     wp.blocks.registerBlockStyle("core/heading", [
//         {
//             name: "small",
//             label: "Small",
//         },
//     ]);
// }); 

wp.blocks.registerBlockVariation(
    'core/group',
    {
        name: 'container',
        title: 'Full width container',
        attributes: {
            align: 'full'
        },
        isDefault: true
    }
);