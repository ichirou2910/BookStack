import mermaid from "mermaid";
import pako from 'pako';

const placeholder = `sequenceDiagram
    Alice->>+John: Hello John, how are you?
    Alice->>+John: John, can you hear me?
    John-->>-Alice: Hi Alice, I can hear you!
    John-->>-Alice: I feel great!`;

function elemIsMermaidBlock(elem) {
    return elem.tagName.toLowerCase() === 'mermaid-block';
}

/**
 * @param {Editor} editor
 * @param {String} code
 * @param {String} language
 * @param {function(string, string)} callback (Receives (code: string,language: string)
 */
function showPopup(editor, code, language, callback) {
    /** @var {CodeEditor} codeEditor * */
    const codeEditor = window.$components.first('code-editor');
    const bookMark = editor.selection.getBookmark();
    codeEditor.open(code, language, (newCode, newLang) => {
        callback(newCode, newLang);
        editor.focus();
        editor.selection.moveToBookmark(bookMark);
    }, () => {
        editor.focus();
        editor.selection.moveToBookmark(bookMark);
    });
}

/**
 * @param {Editor} editor
 * @param {MermaidBlockElement} mermaidBlock
 */
function showPopupForMermaidBlock(editor, mermaidBlock) {
    showPopup(editor, mermaidBlock.getContent(), "", (newCode, newLang) => {
        mermaidBlock.setContent(newCode, newLang);
    });
}

/**
 * Define our custom mermaid-block HTML element that we use.
 * Needs to be delayed since it needs to be defined within the context of the
 * child editor window and document, hence its definition within a callback.
 * @param {Editor} editor
 */
function defineMermaidBlockCustomElement(editor) {
    const doc = editor.getDoc();
    const win = doc.defaultView;

    class MermaidBlockElement extends win.HTMLElement {

        /**
         * @type {?SimpleEditorInterface}
         */
        editor = null;

        constructor() {
            super();
        }

        setContent(content, language) {
            if (this.editor) {
                this.editor.setContent(content);
                this.editor.setMode(language, content);
            }

            const svg = this.querySelector('svg');
            mermaid.render('mermaidDiagram', content, function (svgCode) {
                svg.outerHTML = postProcessSvg(svgCode, content);
            });

        }

        getContent() {
            const svg = this.querySelector('svg')

            // Decompress data
            const data = svg.getAttribute('text-data');
            if (!data)
                return "";
            return new TextDecoder().decode(pako.inflate(Uint8Array.from(atob(data), (c) => c.codePointAt(0))));
        }
    }

    win.customElements.define('mermaid-block', MermaidBlockElement);
}

/**
 * @param {Editor} editor
 */
function register(editor) {
    editor.ui.registry.addIcon('mermaidblock', '<?xml version="1.0" encoding="utf-8"?> <svg viewBox="269.414 74.385 17.578 17.578" width="17.578" height="17.578" xmlns="http://www.w3.org/2000/svg"><path d="M 286.992 79.179 L 286.992 74.385 L 282.198 74.385 L 282.198 75.983 L 274.208 75.983 L 274.208 74.385 L 269.414 74.385 L 269.414 79.179 L 271.012 79.179 L 271.012 87.169 L 269.414 87.169 L 269.414 91.963 L 274.208 91.963 L 274.208 90.365 L 282.198 90.365 L 282.198 91.963 L 286.992 91.963 L 286.992 87.169 L 285.394 87.169 L 285.394 79.179 L 286.992 79.179 Z M 271.012 75.983 L 272.61 75.983 L 272.61 77.58 L 271.012 77.58 L 271.012 75.983 Z M 272.61 90.365 L 271.012 90.365 L 271.012 88.767 L 272.61 88.767 L 272.61 90.365 Z M 282.198 88.767 L 274.208 88.767 L 274.208 87.169 L 272.61 87.169 L 272.61 79.179 L 274.208 79.179 L 274.208 77.58 L 282.198 77.58 L 282.198 79.178 L 283.796 79.178 L 283.796 87.168 L 282.198 87.168 L 282.198 88.767 Z M 285.394 90.365 L 283.796 90.365 L 283.796 88.767 L 285.394 88.767 L 285.394 90.365 Z M 283.796 77.58 L 283.796 75.983 L 285.394 75.983 L 285.394 77.58 L 283.796 77.58 Z" transform="matrix(1, 0, 0, 1, 0, -1.4210854715202004e-14)"/><path d="M 277.51 86.222 C 277.72 86.444 277.72 86.813 277.51 86.959 C 277.299 87.254 276.879 87.254 276.669 86.959 L 273.586 83.937 C 273.332 83.701 273.332 83.286 273.586 83.051 L 276.669 79.955 C 276.879 79.733 277.3 79.733 277.51 79.955 C 277.719 80.181 277.719 80.541 277.51 80.766 L 274.847 83.495 L 277.51 86.222 Z M 278.873 80.748 C 278.686 80.498 278.72 80.208 278.934 79.984 C 279.146 79.759 279.486 79.768 279.714 80.011 L 282.797 83.033 C 283.05 83.268 283.05 83.683 282.797 83.919 L 279.714 87.015 C 279.504 87.236 279.084 87.236 278.873 87.015 C 278.663 86.79 278.663 86.429 278.873 86.204 L 281.535 83.476 L 278.873 80.748 Z" style="" transform="matrix(1, 0, 0, 1, 0, -1.4210854715202004e-14)"/></svg>');

    editor.ui.registry.addButton('mermaideditor', {
        tooltip: 'Insert mermaid diagram',
        icon: 'mermaidblock',
        onAction() {
            editor.execCommand('mermaideditor');
        },
    });

    editor.ui.registry.addButton('editmermaid', {
        tooltip: 'Edit diagram',
        icon: 'edit-block',
        onAction() {
            editor.execCommand('mermaideditor');
        },
    });

    editor.addCommand('mermaideditor', () => {
        const selectedNode = editor.selection.getNode();
        if (elemIsMermaidBlock(selectedNode)) {
            showPopupForMermaidBlock(editor, selectedNode);
        } else {
            let textContent = editor.selection.getContent({ format: 'text' });
            if (!textContent)
                textContent = placeholder;
            showPopup(editor, textContent, '', (newCode, _) => {
                mermaid.render('mermaidDiagram', newCode, function (svgCode) {
                    editor.insertContent(postProcessSvg(svgCode, newCode));
                });
            });
        }
    });

    editor.on('dblclick', () => {
        const selectedNode = editor.selection.getNode();
        if (elemIsMermaidBlock(selectedNode)) {
            showPopupForMermaidBlock(editor, selectedNode);
        }
    });

    editor.on('PreInit', () => {
        mermaid.initialize({ startOnLoad: true, theme: "neutral" });

        editor.parser.addNodeFilter('svg', elms => {
            for (const el of elms) {
                var html = el.outerHTML;
                const wrapper = window.tinymce.html.Node.create('mermaid-block', {
                    contenteditable: 'false',
                });
                el.wrap(wrapper);
                el.outerHTML = html;
            }
        });

        editor.parser.addNodeFilter('mermaid-block', elms => {
            for (const el of elms) {
                el.attr('contenteditable', 'false');
            }
        });

        editor.serializer.addNodeFilter('mermaid-block', elms => {
            for (const el of elms) {
                el.unwrap();
            }
        });
    });

    editor.ui.registry.addContextToolbar('mermaideditor', {
        predicate(node) {
            return node.nodeName.toLowerCase() === 'mermaid-block';
        },
        items: 'editmermaid',
        position: 'node',
        scope: 'node',
    });

    editor.on('PreInit', () => {
        defineMermaidBlockCustomElement(editor);
    });
}

function postProcessSvg(svgCode, content) {
    let svg = svgCode;

    // Embed mermaid code into the svg
    const compressedData = content ? btoa(String.fromCharCode.apply(null, pako.deflate(content))) : "";
    svg = svgCode.replace('<svg', `<svg text-data="${compressedData}" width="100%"`);

    // Fill background color
    svg = svg.replace('mermaidDiagram{', `mermaidDiagram{background-color:#FFFFFF;`);

    return svg;
}

/**
 * @return {register}
 */
export function getPlugin() {
    return register;
}

