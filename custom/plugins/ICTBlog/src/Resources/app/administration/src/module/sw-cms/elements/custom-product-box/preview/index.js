import template from './sw-cms-el-preview-custom-product-box.html.twig';
import './sw-cms-el-preview-custom-product-box.scss';

const { Component } = Shopware;

/**
 * @private since v6.5.0
 * @package content
 */
Component.register('sw-cms-el-preview-custom-product-box', {
    template,
});
