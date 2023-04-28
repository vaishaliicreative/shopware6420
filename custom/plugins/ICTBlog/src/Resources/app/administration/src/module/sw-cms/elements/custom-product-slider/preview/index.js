import template from './sw-cms-el-preview-custom-product-slider.html.twig';
import './sw-cms-el-preview-custom-product-slider.scss';

const { Component } = Shopware;

/**
 * @private since v6.5.0
 * @package content
 */
Component.register('sw-cms-el-preview-custom-product-slider', {
    template,
});
