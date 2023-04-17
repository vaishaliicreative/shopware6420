/*
 * @package inventory
 */

Shopware.Service('privileges')
    .addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'catalogues',
        key: 'task',
        roles: {
            viewer: {
                privileges: [
                    'task:read',
                    'custom_field_set:read',
                    'custom_field:read',
                    'custom_field_set_relation:read',
                    Shopware.Service('privileges').getPrivileges('media.viewer'),
                    'user_config:read',
                    'user_config:create',
                    'user_config:update',
                ],
                dependencies: [],
            },
            editor: {
                privileges: [
                    'task:update',
                    Shopware.Service('privileges').getPrivileges('media.creator'),
                ],
                dependencies: [
                    'task.viewer',
                ],
            },
            creator: {
                privileges: [
                    'task:create',
                ],
                dependencies: [
                    'task.viewer',
                    'task.editor',
                ],
            },
            deleter: {
                privileges: [
                    'task:delete',
                ],
                dependencies: [
                    'task.viewer',
                ],
            },
        },
    });
