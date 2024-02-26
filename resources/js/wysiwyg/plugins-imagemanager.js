/**
 * @param {Editor} editor
 */
function register(editor) {
    // Custom Image picker button
    editor.ui.registry.addButton('imagemanager-insert', {
        title: 'Insert image',
        icon: 'image',
        tooltip: 'Insert image',
        onAction() {
            /** @type {ImageManager} * */
            const imageManager = window.$components.first('image-manager');
            imageManager.show(image => {
                let html = `<figure contenteditable="false" class="image align-center">`;
                html += `<img src="${image.url}" alt="${image.name}">`;
                html += '<figcaption contenteditable="true"></figcaption';
                html += '</figure>';
                editor.execCommand('mceInsertContent', false, html);
            }, 'gallery');
        },
    });
}

/**
 * @return {register}
 */
export function getPlugin() {
    return register;
}
