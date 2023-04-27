import './component';
import './preview';

/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'custom-youtube-video',
    label: 'sw-cms-text-blog.blocks.video.customYoutubeVideo.label',
    category: 'video',
    component: 'sw-cms-block-custom-youtube-video',
    previewComponent: 'sw-cms-preview-custom-youtube-video',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        customVideo: 'custom-youtube-video',
    },
});
