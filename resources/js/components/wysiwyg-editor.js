import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function uploadImage(uploadUrl, file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', csrfToken);

    return fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
    }).then(async (response) => {
        if (!response.ok) {
            throw new Error(`Upload failed (${response.status})`);
        }

        const data = await response.json();

        return data.location;
    });
}

function syncTextarea(editor, textarea) {
    textarea.value = editor.getHTML();
}

function updateToolbarState(editor, root) {
    const activeClasses = ['bg-gray-200', 'text-gray-800'];

    const toggles = [
        ['bold', '[data-hs-editor-bold]'],
        ['italic', '[data-hs-editor-italic]'],
        ['underline', '[data-hs-editor-underline]'],
        ['strike', '[data-hs-editor-strike]'],
        ['heading', '[data-hs-editor-h2]', { level: 2 }],
        ['heading', '[data-hs-editor-h3]', { level: 3 }],
        ['bulletList', '[data-hs-editor-ul]'],
        ['orderedList', '[data-hs-editor-ol]'],
        ['blockquote', '[data-hs-editor-blockquote]'],
        ['code', '[data-hs-editor-code]'],
        ['link', '[data-hs-editor-link]'],
        ['textAlign', '[data-hs-editor-align-left]', { align: 'left' }],
        ['textAlign', '[data-hs-editor-align-center]', { align: 'center' }],
        ['textAlign', '[data-hs-editor-align-right]', { align: 'right' }],
    ];

    toggles.forEach(([name, selector, attrs]) => {
        const button = root.querySelector(selector);

        if (!button) {
            return;
        }

        const isActive = editor.isActive(name, attrs);
        activeClasses.forEach((className) => button.classList.toggle(className, isActive));
    });
}

function bindAction(root, selector, handler) {
    const button = root.querySelector(selector);

    if (!button) {
        return;
    }

    button.addEventListener('click', (event) => {
        event.preventDefault();
        handler();
    });
}

function initPrelineEditor(root) {
    const editorField = root.querySelector('[data-hs-editor-field]');
    const textareaId = root.dataset.textareaId ?? '';
    const fieldTextarea = document.getElementById(textareaId)
        ?? root.parentElement?.querySelector(`textarea[name="${root.dataset.fieldName ?? ''}"]`);

    if (!editorField || !fieldTextarea) {
        console.error('[wysiwyg-editor] Missing editor field or textarea.', {
            editorField,
            textareaId,
            fieldName: root.dataset.fieldName,
        });

        return;
    }

    if (root.dataset.editorInitialized === 'true') {
        return;
    }

    root.dataset.editorInitialized = 'true';

    const uploadUrl = root.dataset.uploadUrl ?? '';
    const minHeight = Number.parseInt(root.dataset.minHeight ?? '360', 10);
    const placeholder = root.dataset.placeholder ?? 'พิมพ์รายละเอียด...';
    const initialContent = fieldTextarea.value;

    const editor = new Editor({
        element: editorField,
        content: initialContent,
        editorProps: {
            attributes: {
                class: 'tiptap relative p-4 text-sm text-gray-800 focus:outline-none dark:text-white/90',
                style: `min-height: ${minHeight}px`,
            },
        },
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
            }),
            Placeholder.configure({
                placeholder,
                emptyEditorClass: 'is-editor-empty',
            }),
            Underline,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    class: 'text-brand-600 underline decoration-2 hover:text-brand-700 font-medium',
                },
            }),
            Image.configure({
                HTMLAttributes: {
                    class: 'max-w-full h-auto rounded-lg',
                },
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
        ],
        onUpdate: ({ editor: ed }) => {
            syncTextarea(ed, fieldTextarea);
            updateToolbarState(ed, root);
        },
        onSelectionUpdate: ({ editor: ed }) => {
            updateToolbarState(ed, root);
        },
    });

    syncTextarea(editor, fieldTextarea);
    updateToolbarState(editor, root);

    bindAction(root, '[data-hs-editor-bold]', () => editor.chain().focus().toggleBold().run());
    bindAction(root, '[data-hs-editor-italic]', () => editor.chain().focus().toggleItalic().run());
    bindAction(root, '[data-hs-editor-underline]', () => editor.chain().focus().toggleUnderline().run());
    bindAction(root, '[data-hs-editor-strike]', () => editor.chain().focus().toggleStrike().run());
    bindAction(root, '[data-hs-editor-h2]', () => editor.chain().focus().toggleHeading({ level: 2 }).run());
    bindAction(root, '[data-hs-editor-h3]', () => editor.chain().focus().toggleHeading({ level: 3 }).run());
    bindAction(root, '[data-hs-editor-ul]', () => editor.chain().focus().toggleBulletList().run());
    bindAction(root, '[data-hs-editor-ol]', () => editor.chain().focus().toggleOrderedList().run());
    bindAction(root, '[data-hs-editor-blockquote]', () => editor.chain().focus().toggleBlockquote().run());
    bindAction(root, '[data-hs-editor-code]', () => editor.chain().focus().toggleCode().run());
    bindAction(root, '[data-hs-editor-align-left]', () => editor.chain().focus().setTextAlign('left').run());
    bindAction(root, '[data-hs-editor-align-center]', () => editor.chain().focus().setTextAlign('center').run());
    bindAction(root, '[data-hs-editor-align-right]', () => editor.chain().focus().setTextAlign('right').run());
    bindAction(root, '[data-hs-editor-undo]', () => editor.chain().focus().undo().run());
    bindAction(root, '[data-hs-editor-redo]', () => editor.chain().focus().redo().run());

    bindAction(root, '[data-hs-editor-link]', () => {
        const previousUrl = editor.getAttributes('link').href;
        const url = window.prompt('URL', previousUrl ?? 'https://');

        if (url === null) {
            return;
        }

        if (url === '') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();

            return;
        }

        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    });

    const imageInput = root.querySelector('[data-hs-editor-image-input]');

    bindAction(root, '[data-hs-editor-image]', () => imageInput?.click());

    imageInput?.addEventListener('change', async () => {
        const file = imageInput.files?.[0];

        if (!file) {
            return;
        }

        try {
            const location = await uploadImage(uploadUrl, file);
            editor.chain().focus().setImage({ src: location }).run();
        } catch (error) {
            window.alert(error instanceof Error ? error.message : 'อัปโหลดรูปไม่สำเร็จ');
        }

        imageInput.value = '';
    });

    root.querySelectorAll('[data-hs-editor-emoji-item]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const emoji = button.getAttribute('data-hs-editor-emoji-item');

            if (emoji) {
                editor.chain().focus().insertContent(emoji).run();
            }
        });
    });

    root.closest('form')?.addEventListener('submit', () => {
        syncTextarea(editor, fieldTextarea);
    });
}

function initEditors() {
    document.querySelectorAll('[data-hs-editor]').forEach((root) => {
        try {
            initPrelineEditor(root);
        } catch (error) {
            console.error('[wysiwyg-editor] Failed to initialize editor.', error);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditors);
} else {
    initEditors();
}
