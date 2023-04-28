import "./preview";
import "./config";
import "./component";

Shopware.Service('cmsService').registerCmsElement({
    name: 'custom-youtube-video',
    label: 'sw-cms-text-blog.elements.customYoutubeVideo.label',
    component: 'sw-cms-el-custom-youtube-video',
    configComponent: 'sw-cms-el-config-custom-youtube-video',
    previewComponent: 'sw-cms-el-preview-custom-youtube-video',
    defaultConfig: {
        videoID: {
            source: 'static',
            value: '',
            required: true,
        },
        autoPlay: {
            source: 'static',
            value: false,
        },
        loop: {
            source: 'static',
            value: false,
        },
        showControls: {
            source: 'static',
            value: true,
        },
        start: {
            source: 'static',
            value: null,
        },
        end: {
            source: 'static',
            value: null,
        },
        displayMode: {
            source: 'static',
            value: 'standard',
        },
        advancedPrivacyMode: {
            source: 'static',
            value: true,
        },
        needsConfirmation: {
            source: 'static',
            value: false,
        },
        previewMedia: {
            source: 'static',
            value: null,
            entity: {
                name: 'media',
            },
        },
    },
});
