/*
 * @package inventory
 */

import './page/sw-blog-category-list';
import './page/sw-blog-category-detail';
import './acl';
import './snippet/en-GB.json';
import './snippet/de-DE.json';
import defaultSearchConfiguration from './default-search-configuration';

const { Module } = Shopware;

Module.register('sw-blog-category',{
    type: 'core',
    name: 'blog-task',
    title: 'sw-blog-task.general.mainMenuItemGeneral',
    description: 'Manages the blog category of the application',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'regular-products',
    favicon: 'icon-module-products.png',
    entity: 'ict_blog_category',

    routes: {
        index: {
            components: {
                default: 'sw-blog-category-list',
            },
            path: 'index',
            meta: {
                privilege: 'blog_category.viewer',
            },
        },
        create: {
            component: 'sw-blog-category-detail',
            path: 'create',
            meta: {
                parentPath: 'sw.blog-category.index',
                privilege: 'blog_category.creator',
            },
        },
        detail: {
            component: 'sw-blog-category-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sw.blog-category.index',
                privilege: 'blog_category.viewer',
            },
            props: {
                default(route) {
                    return {
                        categoryId: route.params.id,
                    };
                },
            },
        },
    },

    navigation: [{
        path: 'sw.blog-category.index',
        privilege: 'blog_category.viewer',
        label: 'sw-blog-category.general.mainMenuItemList',
        id: 'sw-blog-category',
        parent: 'sw-catalogue',
        color: '#57D9A3',
        position: 50,
    }],

    defaultSearchConfiguration,
});
