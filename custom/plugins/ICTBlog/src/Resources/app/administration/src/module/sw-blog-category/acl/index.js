/*
 * @package inventory
 */

Shopware.Service('privileges')
    .addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'catalogues',
        key: 'blog_category',
        roles: {
            viewer: {
                privileges: [
                    'blog_category:read',
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
                    'blog_category:update',
                    Shopware.Service('privileges').getPrivileges('media.creator'),
                ],
                dependencies: [
                    'blog_category.viewer',
                ],
            },
            creator: {
                privileges: [
                    'blog_category:create',
                ],
                dependencies: [
                    'blog_category.viewer',
                    'blog_category.editor',
                ],
            },
            deleter: {
                privileges: [
                    'blog_category:delete',
                ],
                dependencies: [
                    'blog_category.viewer',
                ],
            },
        },
    });
