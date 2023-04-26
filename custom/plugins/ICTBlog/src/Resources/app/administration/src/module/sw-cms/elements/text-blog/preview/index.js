import template from './sw-cms-el-preview-text-blog.html.twig';
import './sw-cms-el-preview-text-blog.scss';

const { Component } = Shopware;

/**
 * @private since v6.5.0
 * @package content
 */
Component.register('sw-cms-el-preview-text-blog', {
    template,
});
