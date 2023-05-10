import './view/sw-login-login';

const { Module } = Shopware;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-login', {
    type: 'core',
    name: 'login',
    title: 'sw-login.general.mainMenuItemsGeneral',
    description: 'sw-login.general.description',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#F19D12',

    routes: {
        index: {
            component: 'sw-login',
            path: '/login',
            alias: '/signin',
            coreRoute: true,
            redirect: {
                name: 'sw.login.index.login',
            },
            props: {
                default: (route) => {
                    return {
                        hash: route.params.hash,
                    };
                },
            },
            children: {
                login: {
                    component: 'sw-login-login',
                    path: '',
                },
                recovery: {
                    component: 'sw-login-recovery',
                    path: 'recovery',
                },
                recoveryInfo: {
                    component: 'sw-login-recovery-info',
                    path: 'info',
                },
                userRecovery: {
                    component: 'sw-login-recovery-recovery',
                    path: 'user-recovery/:hash',
                },
                // verify: {
                //     component: 'sw-login-verify',
                //     path: 'verify',
                // },
            },
        },
    },
});
