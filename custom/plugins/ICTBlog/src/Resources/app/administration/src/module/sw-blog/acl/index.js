/*
 * @package inventory
 */

Shopware.Service('privileges')
    .addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'catalogues',
        key: 'blog',
        roles: {
            viewer: {
                privileges: [
                    'blog:read',
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
                    'blog:update',
                    Shopware.Service('privileges').getPrivileges('media.creator'),
                ],
                dependencies: [
                    'blog.viewer',
                ],
            },
            creator: {
                privileges: [
                    'blog:create',
                ],
                dependencies: [
                    'blog.viewer',
                    'blog.editor',
                ],
            },
            deleter: {
                privileges: [
                    'blog:delete',
                ],
                dependencies: [
                    'blog.viewer',
                ],
            },
        },
    });
