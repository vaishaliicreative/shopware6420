import './page/ict-core-import-config'

const {Module} = Shopware;

Module.register('ict-core-import-config', {
    type: 'plugin',
    name: 'ictCoreImportConfig',
    title: 'ict-core-import-config.general.mainMenuItemGeneral',
    description: 'ict-core-import-config.general.mainMenuItemDescription',
    color: '#FFD700',
    icon: 'regular-gift',
    routes: {
        index: {
            component: 'ict-core-import-config',
            path: 'index',
        },
    },
    navigation: [{
        label: 'ict-core-import-config.general.mainMenuItemGeneral',
        path: 'ict.core.import.config.index',
        parent: 'sw-catalogue',
        position: 100,
    }],
})
