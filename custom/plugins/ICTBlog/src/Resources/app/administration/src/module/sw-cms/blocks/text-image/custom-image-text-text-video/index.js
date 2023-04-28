import CMS from '../../../constant/sw-cms.constant';
import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'custom-image-text-text-video',
    label: 'sw-cms-text-blog.blocks.textImage.customImageTextTextVideo.label',
    category: 'text-image',
    component: 'sw-cms-block-custom-image-text-text-video',
    previewComponent: 'sw-cms-preview-custom-image-text-text-video',
    defaultConfig: {
        marginBottom: null,
        marginTop: null,
        marginLeft: null,
        marginRight: null,
    },
    slots: {
        'firstImage':{
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
        'firstText':{
            type:'text-blog',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <h2 style="text-align: center;">Custom Image Text video</h2>
                        <p style="text-align: center;">An English diarist and naval administrator.
                        I served as administrator of the Royal Navy and Member of Parliament.</p>
                        `.trim(),
                    },
                },
            },
        },
        'secondText': {
            type:'text-blog',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <h2 style="text-align: center;">Custom Image Text video</h2>
                        <p style="text-align: center;">An English diarist and naval administrator.
                        I served as administrator of the Royal Navy and Member of Parliament.</p>
                        `.trim(),
                    },
                },
            },
        },
        'videoContent':{
            type:'custom-youtube-video'
        }
    },
});
