import './component';
import './preview';

/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'custom-product-youtube-video-description',
    label: 'sw-cms-text-blog.blocks.textImage.customTextVideoImage.label',
    category: 'text-image',
    component: 'sw-cms-block-custom-product-youtube-video-description',
    previewComponent: 'sw-cms-preview-custom-product-youtube-video-description',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
        backgroundMedia: {
            url: '/administration/static/img/cms/preview_mountain_large.jpg',
        },
    },
    slots: {
        leftText: {
            type: 'text-blog',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p style="text-align: center; color: #000000">An English diarist and naval administrator.
                        I served as administrator of the Royal Navy and Member of Parliament.</p>
                        `.trim(),
                    },
                },
            },
        },
        rightVideoFirst:{
            type:'custom-youtube-video'
        },
        rightDescriptionFirst:{
            type:'text-blog'
        },
        rightVideoSecond:{
            type:'custom-youtube-video'
        },
        rightDescriptionSecond:{
            type:'text-blog'
        },
    },
});
