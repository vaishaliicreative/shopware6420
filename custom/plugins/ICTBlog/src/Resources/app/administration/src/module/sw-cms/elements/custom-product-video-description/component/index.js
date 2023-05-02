import template from './sw-cms-el-custom-product-video-description.html.twig';
import './sw-cms-el-custom-product-video-description.scss'

const { Component, Mixin,Filter } = Shopware;
Component.register('sw-cms-el-custom-product-video-description', {
    template,
    mixins: [
        Mixin.getByName('cms-element'),
        Mixin.getByName('placeholder'),
    ],
    computed: {
        product() {
            if (this.currentDemoEntity) {
                return this.currentDemoEntity;
            }

            if (!this.element.data || !this.element.data.product) {
                return {
                    name: 'Product information',
                    customFields:
                        {
                            custom_product_video_description_video_description_first: `Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
                                  tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
                                  At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren,
                                  no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
                                  consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
                                  dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo
                                  dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem
                                  ipsum dolor sit amet.`,
                        },
                };
            }
            console.log(this.element.data.product);
            return this.element.data.product;
        },

        pageType() {
            return this.cmsPageState?.currentPage?.type;
        },

        isProductPageType() {
            return this.pageType === 'product_detail';
        },

        currentDemoEntity() {
            if (this.cmsPageState.currentMappingEntity === 'product') {
                return this.cmsPageState.currentDemoEntity;
            }

            return null;
        },
    },

    watch: {
        pageType(newPageType) {
            this.$set(this.element, 'locked', (newPageType === 'product_detail'));
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('custom-product-video-description');
            this.initElementData('custom-product-video-description');
            this.$set(this.element, 'locked', this.isProductPageType);
        },
    },
})
