import template from './sw-login-login.html.twig';
// import getErrorCode from 'src/core/data/error-codes/login.error-codes';
const { Component, Mixin,Context, Application } = Shopware;

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
            loginUserDiv: '',
            loginOtpDiv:'',
            otp: '',
            resendOptDiv:'',
        };
    },
    computed: {
        showLoginAlert() {
            return typeof this.loginAlertMessage === 'string' && this.loginAlertMessage.length >= 1;
        },
    },
    created() {
        this.loginUserDiv = true;
        this.loginOtpDiv = false;
        // console.log("login component");
        if (!localStorage.getItem('sw-admin-locale')) {
            Shopware.State.dispatch('setAdminLocale', navigator.language);
        }
    },

    methods: {

       createUserOtpWithEmail() {
           // alert("test");
           // console.log(this.username);
           // this.$emit('is-loading');
           // let headers = this.configService.getBasicHeaders();

           return Application.getContainer('init').httpClient
               .post('/backend/login/generateotp', {
                   username: this.username
               },{
                   baseURL: Context.api.apiPath,
               }).then((response) => {
                   // console.log(response.data.type);
                   if(response.data.type == 'success'){
                       this.loginUserDiv = false;
                       this.loginOtpDiv = true;
                   }
                   // this.$emit('is-not-loading');
                   // localStorage.setItem('username',this.username);
                   // this.$router.push({
                   //     name: 'sw.verify.index.verify',
                   // });
                   // console.log(this.$router);
               });
           // this.$emit('is-not-loading');
           // console.log(this.$router);

       },
        verifyOtpWithEmail(){
            this.$emit('is-loading');
            // console.log(this.username);
            return Application.getContainer('init').httpClient
                .post('/backend/login/verifyotp',{
                    username: this.username,
                    otp: this.otp,
                    grant_type: 'password',
                    client_id: 'administration',
                    scopes: 'write'
                },{
                    baseURL: Context.api.apiPath,
                }).then((response) => {
                    // console.log(response);
                    if(response.data.type == 'success') {
                        const auth = this.loginService.setBearerAuthentication({
                            access: response.data.access_token,
                            refresh: response.data.refresh_token,
                            expiry: response.data.expires_in,
                        });
                        this.handleLoginSuccess();
                        return auth;
                    }
                    this.$emit('is-not-loading');
                })
                .catch((response) => {
                    this.otp = '';

                    this.handleLoginError(response);
                    this.$emit('is-not-loading');
                });
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

        handleLoginError(response) {

            this.$emit('login-error');
            setTimeout(() => {
                this.$emit('login-error');
            }, 500);

            this.createNotificationFromResponse(response);
        },


    },

});
