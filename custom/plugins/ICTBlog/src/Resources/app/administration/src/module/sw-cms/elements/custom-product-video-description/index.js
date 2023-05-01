import "./component"
import "./preview"
import "./config"

const Criteria = Shopware.Data.Criteria;
const criteria = new Criteria(1, 25);
criteria.addAssociation('properties');

Shopware.Service('cmsService').registerCmsElement({
    name: 'custom-product-video-description',
    label: 'sw-cms-text-blog.elements.customProductVideoDescription.label',
    component: 'sw-cms-el-custom-product-video-description',
    configComponent: 'sw-cms-el-config-custom-product-video-description',
    previewComponent: 'sw-cms-el-preview-custom-product-video-description',
    disabledConfigInfoTextKey: 'sw-cms.elements.productDescriptionReviews.infoText.descriptionAndReviewsElement',
    defaultConfig: {
        product: {
            source: 'static',
            value: null,
            required: true,
            entity: {
                name: 'product',
                criteria: criteria,
            },
        },
        alignment: {
            source: 'static',
            value: null,
        },
    },
    collect: Shopware.Service('cmsService').getCollectFunction(),
});
