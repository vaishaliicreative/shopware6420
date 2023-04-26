import "./component";
import "./preview";
import CMS from '../../../constant/sw-cms.constant';
/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'image-blog',
    label: 'sw-cms-text-blog.blocks.image.image-blog.label',
    category: 'image',
    component: 'sw-cms-block-image-blog',
    previewComponent: 'sw-cms-preview-image-blog',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        image: {
            type: 'image-blog',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'standard' },
                },
                data: {
                    media: {
                        value: CMS.MEDIA.previewGlasses,
                        source: 'default',
                    },
                },
            },
        },

    },
});
