import './component';
import './config';
import './preview';

const Criteria = Shopware.Data.Criteria;
const criteria = new Criteria(1, 25);
criteria.addAssociation('cover');

/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsElement({
    name: 'custom-product-box',
    label: 'sw-cms.elements.productBox.label',
    component: 'sw-cms-el-custom-product-box',
    previewComponent: 'sw-cms-el-preview-custom-product-box',
    configComponent: 'sw-cms-el-config-custom-product-box',
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
        boxLayout: {
            source: 'static',
            value: 'standard',
        },
        displayMode: {
            source: 'static',
            value: 'standard',
        },
        verticalAlign: {
            source: 'static',
            value: null,
        },
    },
    defaultData: {
        boxLayout: 'standard',
        product: null,
    },
    collect: Shopware.Service('cmsService').getCollectFunction(),
});
