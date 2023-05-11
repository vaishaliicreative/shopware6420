import template from './sw-login-login.html.twig';
// import getErrorCode from 'src/core/data/error-codes/login.error-codes';
const { Component, Mixin } = Shopware;

Component.override('sw-login-login', {
    template,
    inject: ['configService'],
    //
    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            username: '',
            loginAlertMessage: '',
        };
    },
    computed: {
        showLoginAlert() {
            return typeof this.loginAlertMessage === 'string' && this.loginAlertMessage.length >= 1;
        },
    },
    created() {
        // console.log("login component");
        if (!localStorage.getItem('sw-admin-locale')) {
            Shopware.State.dispatch('setAdminLocale', navigator.language);
        }
    },

    methods: {
        loginUserWithPassword() {
            this.$emit('is-loading');

            return this.loginService.loginByUsername(this.username)
                .then(() => {
                    this.handleLoginSuccess();
                    this.$emit('is-not-loading');
                })
                .catch((response) => {
                    this.password = '';

                    this.handleLoginError(response);
                    this.$emit('is-not-loading');
                });
        },

       createUserOtpWithEmail(){
            alert("test");
            // console.log(this.username);
            // console.log(this);
            // this.$emit('is-loading');
            let headers = this.configService.getBasicHeaders();
            let seconds = 0;
            return this.configService.httpClient
                .post('/backend/login/generateotp',{
                    params:{
                        username:this.username
                    },headers
                }).then( (response) => {
                    // console.log(response.data.type);
                    // this.$emit('is-not-loading');
                    this.$router.push({
                        name: 'sw.verify.verify',
                        params: {
                            waitTime: seconds,
                        },
                    });
                    console.log(this.$router);
                    // this.$router.push({ name:'sw.login.index.verify' });
                });
            // this.$emit('is-not-loading');
            // console.log(this.$router);

        },

        handleLoginSuccess() {

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

            this.$emit('login-error');
            setTimeout(() => {
                this.$emit('login-error');
            }, 500);

            this.createNotificationFromResponse(response);
        },


    },

});
