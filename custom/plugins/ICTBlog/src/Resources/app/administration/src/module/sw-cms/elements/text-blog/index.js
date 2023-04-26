import './component';
import './config';
import './preview';

/**
 * @private since v6.5.0
 * @package content
 */
Shopware.Service('cmsService').registerCmsElement({
    name: 'text-blog',
    label: 'sw-cms-text-blog.elements.text-blog.label',
    component: 'sw-cms-el-text-blog',
    configComponent: 'sw-cms-el-config-text-blog',
    previewComponent: 'sw-cms-el-preview-text-blog',
    defaultConfig: {
        content: {
            source: 'static',
            value: `
                <h2 style="text-align: center">First Text Image block</h2>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr </p>
            `.trim(),
        },
        verticalAlign: {
            source: 'static',
            value: null,
        },
    },
    
});

