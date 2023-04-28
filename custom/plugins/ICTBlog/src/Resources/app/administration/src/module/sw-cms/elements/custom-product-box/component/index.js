import template from './sw-cms-el-custom-product-box.html.twig';
import './sw-cms-el-custom-product-box.scss';

const { Component, Mixin, Filter } = Shopware;

/**
 * @private since v6.5.0
 * @package content
 */
Component.register('sw-cms-el-custom-product-box', {
    template,

    mixins: [
        Mixin.getByName('cms-element'),
        Mixin.getByName('placeholder'),
    ],

    computed: {
        product() {
            if (!this.element?.data?.product) {
                return {
                    name: 'Custom Product',
                    description: `This is custom product.`,
                    price: [
                        { gross: 20 },
                    ],
                    cover: {
                        media: {
                            url: '/administration/static/img/cms/preview_glasses_large.jpg',
                            alt: 'Custom Product',
                        },
                    },
                };
            }

            return this.element.data.product;
        },

        displaySkeleton() {
            return !this.element?.data?.product;
        },

        mediaUrl() {
            if (this.product.cover && this.product.cover.media) {
                if (this.product.cover.media.id) {
                    return this.product.cover.media.url;
                }

                return this.assetFilter(this.product.cover.media.url);
            }

            return this.assetFilter('administration/static/img/cms/preview_glasses_large.jpg');
        },

        altTag() {
            if (!this.product?.cover?.media?.alt) {
                return null;
            }

            return this.product.cover.media.alt;
        },

        displayModeClass() {
            if (this.element.config.displayMode.value === 'standard') {
                return null;
            }

            return `is--${this.element.config.displayMode.value}`;
        },

        verticalAlignStyle() {
            if (!this.element.config?.verticalAlign?.value) {
                return null;
            }

            return `align-content: ${this.element.config.verticalAlign.value};`;
        },

        assetFilter() {
            return Filter.getByName('asset');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('custom-product-box');
            this.initElementData('custom-product-box');
        },
    },
});
