import template from './sw-login-verify.html.twig';

const { Component,Mixin,Application,Context } = Shopware;

Component.register('sw-login-verify', {
    template,
    inject: ['licenseViolationService','loginService'],
    //
    mixins: [
        Mixin.getByName('notification'),
    ],
    data() {
        return {
            username: '',
            loginAlertMessage: '',
            otp: '',
        };
    },
    computed: {
        showLoginAlert() {
            return typeof this.loginAlertMessage === 'string' && this.loginAlertMessage.length >= 1;
        },
    },
    created() {
        console.log("verify component");
        this.username = localStorage.getItem('username');
        if (!localStorage.getItem('sw-admin-locale')) {
            Shopware.State.dispatch('setAdminLocale', navigator.language);
        }
        this.$emit('is-not-loading');

    },
    methods: {
        verifyOtpWithEmail(){
            this.$emit('is-loading');
            console.log(this.username);
            return Application.getContainer('init').httpClient
                .post('/backend/login/verifyotp',{
                    username: this.username,
                    otp: this.otp,
                    grant_type: 'password',
                    client_id: 'administration',
                    scopes: 'write',
                },{
                    baseURL: Context.api.apiPath,
                }).then((response) => {
                    console.log(response);
                    const auth = this.loginService.setBearerAuthentication({
                        access: response.data.access_token,
                        refresh: response.data.refresh_token,
                        expiry: response.data.expires_in,
                    });
                    this.$emit('is-not-loading');
                    return auth;
                })
                .catch((response) => {
                    this.otp = '';

                    this.handleLoginError(response);
                    this.$emit('is-not-loading');
                });
        },

        handleLoginSuccess() {
            this.otp = '';

            this.$emit('login-success');

            const animationPromise = new Promise((resolve) => {
                setTimeout(resolve, 150);
            });

            if (this.licenseViolationService) {
                this.licenseViolationService.removeTimeFromLocalStorage(this.licenseViolationService.key.showViolationsKey);
            }

            return animationPromise.then(() => {
                this.$parent.isLoginSuccess = false;
                this.forwardLogin();

                const shouldReload = sessionStorage.getItem('sw-login-should-reload');

                if (shouldReload) {
                    sessionStorage.removeItem('sw-login-should-reload');
                    // reload page to rebuild the administration with all dependencies
                    window.location.reload(true);
                }
            });
        },

        forwardLogin() {
            const previousRoute = JSON.parse(sessionStorage.getItem('sw-admin-previous-route'));
            sessionStorage.removeItem('sw-admin-previous-route');

            const firstRunWizard = Shopware.Context.app.firstRunWizard;

            if (firstRunWizard && !this.$router.history.current.name.startsWith('sw.first.run.wizard.')) {
                this.$router.push({ name: 'sw.first.run.wizard.index' });
                return;
            }
            if (previousRoute?.fullPath) {
                this.$router.push(previousRoute.fullPath);
                return;
            }
            this.$router.push({ name: 'core' });
        },

        handleLoginError(response) {
            this.otp = '';

            // this.$emit('login-error');
            // setTimeout(() => {
            //     this.$emit('login-error');
            // }, 500);
            this.$super('handleLoginError', error);
            return;
        },
    },
});
