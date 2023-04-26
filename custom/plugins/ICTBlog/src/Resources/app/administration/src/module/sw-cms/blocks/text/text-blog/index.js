import './component';
import './preview';

/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'text-blog',
    label: 'sw-cms-text-blog.blocks.text.text-blog.label',
    category: 'text',
    component: 'sw-cms-block-text-blog',
    previewComponent: 'sw-cms-preview-text-blog',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        content: 'text-blog'

    },
});
