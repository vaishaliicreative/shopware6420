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
            timer:30,
            interval:null
        };
    },
    computed: {
        showLoginAlert() {
            return typeof this.loginAlertMessage === 'string' && this.loginAlertMessage.length >= 1;
        },
    },
    created() {
        // this.username = localStorage.getItem('username');
        this.username = window.sessionStorage.getItem('username');
        if (!localStorage.getItem('sw-admin-locale')) {
            Shopware.State.dispatch('setAdminLocale', navigator.language);
        }
        this.$emit('is-not-loading');
        // let timeEnd = localStorage.getItem('timerEnd');
        let timeEnd = window.sessionStorage.getItem('timerEnd');
        // console.log(timeEnd);
        if(timeEnd === null){
            this.timer = 30;
        }else if(timeEnd == 0){
            this.timer = 30;
        }else{
            this.timer = timeEnd;
        }
        this.createdComponent();
    },
    beforeDestroy() {
        clearInterval(this.interval);
        window.sessionStorage.removeItem('timerEnd');
    },
    methods: {
        createdComponent(){
            let timer2 = this.timer;
            // let interval = setInterval(function (){
            this.interval = setInterval(function (){
                let seconds = parseInt(timer2 % 60, 10);

                --seconds;
                let displaySeconds = seconds < 10 ? "0" + seconds : seconds;
                if (seconds < 0){
                    clearInterval(this.interval);
                    document.getElementById('countDownId').innerText = "";
                    document.getElementById('resendOtpBtn').style.display = 'inline-block';
                }else {
                    // let countDownElementInnerHTLM = minutes + ':' + seconds;
                    let countDownElementInnerHTLM = 'Resend OTP only after ' + displaySeconds + ' seconds';
                    document.getElementById('countDownId').innerText = countDownElementInnerHTLM;
                    document.getElementById('resendOtpBtn').style.display = 'none';
                    // timer2 = minutes + ':' + seconds;
                    timer2 = seconds;
                    // localStorage.setItem('timerEnd',timer2);
                    window.sessionStorage.setItem('timerEnd',timer2);
                }
            },1000);
        },
        verifyOtpWithEmail(){
            this.$emit('is-loading');
            clearInterval(this.interval);
            // console.log(this.username);
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

                    if(response.data.type === 'success') {
                        const auth = this.loginService.setBearerAuthentication({
                            access: response.data.access_token,
                            refresh: response.data.refresh_token,
                            expiry: response.data.expires_in,
                        });
                        this.handleLoginSuccess();
                        return auth;
                    }else if(response.data.type === 'notfound'){
                        this.createNotificationError({
                            title: 'Error',
                            message: this.$tc('sw-login.detail.pluginNotFoundOtpMessage')
                        });
                    }else {
                        this.createNotificationError({
                            title: this.$tc('sw-login.detail.pluginErrorTitle'),
                            message: this.$tc('sw-login.detail.pluginErrorMessage')
                        });
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
                window.sessionStorage.removeItem('timerEnd');
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

        handleLoginError(error) {
            this.otp = '';

            this.$super('handleLoginError', error);
            return;
        },

        resendOtpWithEmail(){
            console.log('click resend btn');
            this.timer = 30;
            clearInterval(this.interval);
            Application.getContainer('init').httpClient
                .post('/backend/login/generateotp', {
                    username: this.username
                },{
                    baseURL: Context.api.apiPath,
                }).then((response) => {
                if(response.data.type === 'success'){
                    document.getElementById('resendOtpBtn').style.display = 'none';
                    let timer2 = this.timer;

                    let interval = setInterval(function (){
                        let seconds = parseInt(timer2 % 60, 10);

                        --seconds;
                        let displaySeconds = seconds < 10 ? "0" + seconds : seconds;

                        if(seconds < 0){
                            clearInterval(interval);
                            document.getElementById('countDownId').innerText = "";
                            document.getElementById('resendOtpBtn').style.display = 'inline-block';
                        }else{
                            // let countDownElementInnerHTLM = minutes + ':' + seconds;
                            let countDownElementInnerHTLM = 'Resend OTP only after ' + displaySeconds + ' seconds';
                            document.getElementById('countDownId').innerText = countDownElementInnerHTLM;
                            // document.getElementById('resendOtpBtn').style.display = 'none';
                            // timer2 = minutes + ':' + seconds;
                            timer2 = seconds;
                            localStorage.setItem('timerEnd',timer2);
                        }

                    },1000);
                }else if(response.data.type === 'notfound'){
                    this.createNotificationError({
                        title: 'Error',
                        message: this.$tc('sw-login.detail.pluginNotFoundMessage')
                    });
                }else{
                    this.createNotificationError({
                        title: this.$tc('sw-login.detail.pluginErrorTitle'),
                        message: this.$tc('sw-login.detail.pluginErrorMessage')
                    });
                }
            });
        },

    },
});
