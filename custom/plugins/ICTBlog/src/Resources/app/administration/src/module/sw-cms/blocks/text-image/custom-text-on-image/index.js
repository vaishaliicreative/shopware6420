import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'custom-text-on-image',
    label: 'sw-cms-text-blog.blocks.textImage.customTextOnImage.label',
    category: 'text-image',
    component: 'sw-cms-block-custom-text-on-image',
    previewComponent: 'sw-cms-preview-custom-text-on-image',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
        backgroundMedia: {
            url: '/administration/static/img/cms/preview_mountain_large.jpg',
        },
    },
    slots: {
        customContent: {
            type: 'text-blog',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <h2 style="text-align: center; color: #000000">Custom Text on image</h2>
                        <p style="text-align: center; color: #000000">An English diarist and naval administrator.
                        I served as administrator of the Royal Navy and Member of Parliament.</p>
                        `.trim(),
                    },
                },
            },
        },
    },
});
