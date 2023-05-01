import './component';
import './preview';

/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'custom-product-video-description-listing',
    label: 'sw-cms-text-blog.blocks.commerce.customProductVideoDescListing.label',
    category: 'commerce',
    component: 'sw-cms-block-custom-product-video-description-listing',
    previewComponent: 'sw-cms-preview-custom-product-video-description-listing',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        leftAuthor: 'text-blog',
        rightFirst:{
            type:'custom-product-video-description'
        }
    },
});
