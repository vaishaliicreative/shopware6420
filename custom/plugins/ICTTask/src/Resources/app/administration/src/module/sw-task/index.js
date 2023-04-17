/*
 * @package inventory
 */

import './page/sw-task-list';
import './page/sw-task-detail';
import './acl';
import './snippet/en-GB.json';
import './snippet/de-DE.json';
import defaultSearchConfiguration from './default-search-configuration';

const { Module } = Shopware;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-task', {
    type: 'core',
    name: 'task',
    title: 'sw-task.general.mainMenuItemGeneral',
    description: 'Manages the task of the application',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'regular-products',
    favicon: 'icon-module-products.png',
    entity: 'ict_task',

    routes: {
        index: {
            components: {
                default: 'sw-task-list',
            },
            path: 'index',
            meta: {
                privilege: 'task.viewer',
            },
        },
        create: {
            component: 'sw-task-detail',
            path: 'create',
            meta: {
                parentPath: 'sw.task.index',
                privilege: 'task.creator',
            },
        },
        detail: {
            component: 'sw-task-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sw.task.index',
                privilege: 'task.viewer',
            },
            props: {
                default(route) {
                    return {
                        taskId: route.params.id,
                    };
                },
            },
        },
    },

    navigation: [{
        path: 'sw.task.index',
        privilege: 'task.viewer',
        label: 'sw-task.general.mainMenuItemList',
        id: 'sw-task',
        parent: 'sw-catalogue',
        color: '#57D9A3',
        position: 50,
    }],

    defaultSearchConfiguration,
});
