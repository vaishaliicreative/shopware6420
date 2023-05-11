import template from './sw-login-verify.html.twig';

const { Component } = Shopware;

Component.register('sw-login-verify', {
    template,
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            console.log("callllllllllll")
            console.log(this.$router);
            this.$emit('is-not-loading');
        },
    },
});
