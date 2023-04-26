import "./component";
import "./preview";
import CMS from '../../../constant/sw-cms.constant';
/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'blog-text-image',
    label: 'sw-cms-text-blog.blocks.blogTextImage.imageText.label',
    category: 'text-image',
    component: 'sw-cms-block-blog-text-image',
    previewComponent: 'sw-cms-preview-blog-text-image',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        left: {
            type: 'image-blog',
        },
        right: 'text-blog',
    },
});
