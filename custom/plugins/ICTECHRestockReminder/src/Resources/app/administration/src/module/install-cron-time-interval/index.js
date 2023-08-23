import './page/install-cron-time-interval-detail';

const { Module } = Shopware;

Module.register('install-cron-time-interval',{
    type: 'plugin',
    name: 'install-cron-time-interval.name',
    title: 'install-cron-time-interval.title',
    description: 'install-cron-time-interval.description',
    color: '#9AA8B5',
    icon: 'default-time-clock',
    favicon: 'icon-module-settings.png',
    entity: 'scheduled_task',

    routes:{
        index:{
            components:{
                default:'install-cron-time-interval-detail'
            },
            path:'index',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            },
        }
    },

    settingsItem: {
        privilege: 'system.system_config',
        to: 'install.cron.time.interval.index',
        group: 'plugins',
        icon: 'default-time-clock',
    }
});
