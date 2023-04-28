import CMS from '../../../constant/sw-cms.constant';
import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'custom-image-text-video',
    label: 'sw-cms-text-blog.blocks.textImage.customImageTextVideo.label',
    category: 'text-image',
    component: 'sw-cms-block-custom-image-text-video',
    previewComponent: 'sw-cms-preview-custom-image-text-video',
    defaultConfig: {
        marginBottom: null,
        marginTop: null,
        marginLeft: null,
        marginRight: null,
        sizingMode: 'full_width',
    },
    slots: {
        'left-image':{
            type:'image-blog',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' },
                },
                data: {
                    media: {
                        value: CMS.MEDIA.previewCamera,
                        source: 'default',
                    },
                },
            },
        },
        'center-text':{
            type:'text-blog',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <h2 style="text-align: center;">Custom Text image video</h2>
                        <p style="text-align: center;">An English diarist and naval administrator.
                        I served as administrator of the Royal Navy and Member of Parliament.</p>
                        `.trim(),
                    },
                },
            },
        },
        'right-video': {
            type:'custom-youtube-video'
        },
    },
});

