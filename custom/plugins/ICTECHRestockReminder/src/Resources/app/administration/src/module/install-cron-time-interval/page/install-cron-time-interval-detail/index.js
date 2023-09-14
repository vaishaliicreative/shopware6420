import template from "./install-cron-time-interval-detail.html.twig";

const { Component } = Shopware;

Component.register('install-cron-time-interval-detail',{
   template,
    inject: ['configService'],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false
        };
    },

    created() {
        // this.createdComponent();
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        onSave() {
            let headers = this.configService.getBasicHeaders();
            this.isSaveSuccessful = false;
            this.isLoading = true;

            this.$refs.systemConfig.saveAll().then(() => {
                return this.configService.httpClient.get('/ictech/updateScheduledTask',{headers})
                    .then((response) => {
                        this.isLoading = false;
                        this.isSaveSuccessful = true;
                    })
                    .catch((exception) => {
                        this.isLoading = false;
                    });
            }).catch((err) => {
                this.isLoading = false;
                this.createNotificationError({
                    message: err,
                });
            });
        },

        onLoadingChanged(loading) {
            this.isLoading = loading;
        },

        openModal() {
            this.open = true;
        },
        closeModal() {
            this.open = false;
        },
        onCancel() {
            this.$router.push({ name: 'sw.settings.index.system' });
        }
    }
});
