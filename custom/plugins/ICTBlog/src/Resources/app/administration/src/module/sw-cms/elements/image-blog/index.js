import "./preview";
import "./config";
import "./component";

Shopware.Service('cmsService').registerCmsElement({
    name: 'image-blog',
    label: 'sw-cms-text-blog.elements.image-blog.label',
    component: 'sw-cms-el-image-blog',
    configComponent: 'sw-cms-el-config-image-blog',
    previewComponent: 'sw-cms-el-preview-image-blog',
    defaultConfig: {
        media: {
            source: 'static',
            value: null,
            required: true,
            entity: {
                name: 'media',
            },
        },
        displayMode: {
            source: 'static',
            value: 'standard',
        },
        url: {
            source: 'static',
            value: null,
        },
        newTab: {
            source: 'static',
            value: false,
        },
        minHeight: {
            source: 'static',
            value: '340px',
        },
        verticalAlign: {
            source: 'static',
            value: null,
        },
    },
});
