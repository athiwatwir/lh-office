import { Node, mergeAttributes } from '@tiptap/core';

const YOUTUBE_REGEX = /(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/;

export function parseYoutubeVideoId(input) {
    const value = input.trim();

    if (value === '') {
        return null;
    }

    const match = value.match(YOUTUBE_REGEX);

    return match ? match[1] : null;
}

export function toYoutubeEmbedSrc(input) {
    const videoId = parseYoutubeVideoId(input);

    if (!videoId) {
        return null;
    }

    return `https://www.youtube.com/embed/${videoId}`;
}

export const Youtube = Node.create({
    name: 'youtube',
    group: 'block',
    atom: true,
    selectable: true,
    draggable: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-youtube-video] iframe[src]',
                getAttrs: (element) => {
                    const iframe = element.querySelector('iframe');

                    return iframe ? { src: iframe.getAttribute('src') } : false;
                },
            },
            {
                tag: 'iframe[src*="youtube.com/embed"]',
                getAttrs: (element) => ({
                    src: element.getAttribute('src'),
                }),
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            {
                'data-youtube-video': '',
                class: 'youtube-embed-wrapper',
            },
            [
                'iframe',
                mergeAttributes(HTMLAttributes, {
                    class: 'youtube-embed',
                    frameborder: '0',
                    allowfullscreen: 'true',
                    allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
                    referrerpolicy: 'strict-origin-when-cross-origin',
                }),
            ],
        ];
    },

    addCommands() {
        return {
            setYoutubeVideo:
                (options) =>
                ({ commands }) => {
                    if (!options?.src) {
                        return false;
                    }

                    return commands.insertContent({
                        type: this.name,
                        attrs: options,
                    });
                },
        };
    },
});
