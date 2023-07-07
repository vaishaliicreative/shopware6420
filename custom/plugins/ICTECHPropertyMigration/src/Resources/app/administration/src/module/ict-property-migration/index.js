import './page/ict-property-migration';

const { Module } = Shopware;

Module.register('ict-property-migration', {
    type: 'plugin',
    name: 'ictPropertyMigration',
    title: 'ict-property-migration.general.mainMenuItemGeneral',
    description: 'ict-property-migration.general.mainMenuItemDescription',
    color: '#FFD700',
    icon: 'regular-gift',
    routes: {
        index: {
            component: 'ict-property-migration',
            path: 'index',
        },
    },
    navigation: [{
        label: 'ict-property-migration.general.mainMenuItemGeneral',
        path: 'ict.property.migration.index',
        parent: 'sw-catalogue',
        position: 1,
    }],
})
