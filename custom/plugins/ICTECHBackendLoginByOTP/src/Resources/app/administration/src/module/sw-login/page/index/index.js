import template from './sw-login.html.twig';

const { Component } = Shopware;

Component.override('sw-login',{
    template,
    props: {
        hash: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            isLoading: false,
            isLoginSuccess: false,
            isLoginError: false,
        };
    },
    methods: {
        setLoading(val) {
            this.isLoading = val;
        },

        loginError() {
            this.isLoginError = !this.isLoginError;
        },

        loginSuccess() {
            this.isLoginSuccess = !this.isLoginSuccess;
        },
    },
})
