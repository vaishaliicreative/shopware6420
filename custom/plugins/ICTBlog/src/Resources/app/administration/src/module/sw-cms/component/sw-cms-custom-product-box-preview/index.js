import template from './sw-cms-custom-product-box-preview.html.twig';
import './sw-cms-custom-product-box-preview.scss';

const { Component } = Shopware;

/**
 * @private since v6.5.0
 * @package content
 */
Component.register('sw-cms-custom-product-box-preview', {
    template,

    props: {
        hasText: {
            type: Boolean,
            // TODO: Boolean props should only be opt in and therefore default to false
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
            required: false,
        },
    },
});
