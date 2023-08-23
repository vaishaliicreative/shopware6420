import template from "./install-cron-time-interval-detail.html.twig";

const { Component } = Shopware;

Component.register('install-cron-time-interval-detail',{
   template,

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        onSave() {
            this.isSaveSuccessful = false;
            this.isLoading = true;

            this.$refs.systemConfig.saveAll().then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
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
