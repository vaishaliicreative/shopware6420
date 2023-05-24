import template from './sw-login-login.html.twig'
import './sw-login-login.scss';

const { Component, Mixin,Context, Application } = Shopware;

Component.override('sw-login-login', {
    template,
    inject: ['configService'],
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
            timer:30,
            interval:null,
            resendInterval:null
        };
    },
    computed: {
        showLoginAlert() {
            return typeof this.loginAlertMessage === 'string' && this.loginAlertMessage.length >= 1;
        },
    },
    created() {
        let timerEnd = window.sessionStorage.getItem('timerEnd');

        if(timerEnd === null){
            this.timer = 30;
        }else if(timerEnd == 0){
            this.timer = 30;
        }else{
            this.timer = timerEnd;
        }

        let loginUserDiv = window.sessionStorage.getItem('loginUserDiv');
        if(loginUserDiv === null){
            this.loginUserDiv = true;
            this.loginOtpDiv = false;
        }else if(loginUserDiv === 'false'){
            this.loginUserDiv = false;
            this.loginOtpDiv = true;
            this.startTimerAfterLoading();
        }else{
            this.loginUserDiv = true;
            this.loginOtpDiv = false;
        }

        if (!localStorage.getItem('sw-admin-locale')) {
            Shopware.State.dispatch('setAdminLocale', navigator.language);
        }
    },

    methods: {
        createUserOtpWithEmail() {
            this.$emit('is-loading');
            Application.getContainer('init').httpClient
               .post('/backend/login/generateotp', {
                   username: this.username
               },{
                   baseURL: Context.api.apiPath,
               }).then((response) => {
                    this.$emit('is-not-loading');
                    if(response.data.type === 'success'){

                        this.loginUserDiv = false;
                        this.loginOtpDiv = true;
                        window.sessionStorage.setItem('loginUserDiv','false');

                        this.timer = 30;
                        let time2 = 30;

                        this.interval = setInterval(function (){

                            let seconds = parseInt(time2 % 60, 10);

                            --seconds;
                            let displaySeconds = seconds < 10 ? "0" + seconds : seconds;
                            if (seconds < 0){
                                clearInterval(this.interval);
                                clearInterval(this.resendInterval);
                                document.getElementById('countDownId').innerText = "";
                                document.getElementById('resendOtpBtn').style.display = 'inline-block';
                            }else {
                                let countDownElementInnerHTLM = 'Resend OTP only after ' + displaySeconds + ' seconds';
                                document.getElementById('countDownId').innerText = countDownElementInnerHTLM;
                                document.getElementById('resendOtpBtn').style.display = 'none';

                                time2 = seconds;
                                window.sessionStorage.setItem('timerEnd',time2);
                            }
                        },1000)
                        window.sessionStorage.setItem('username',this.username);
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

        verifyOtpWithEmail(){
            this.$emit('is-loading');
            return Application.getContainer('init').httpClient
                .post('/backend/login/verifyotp',{
                    username: this.username,
                    otp: this.otp,
                    grant_type: 'login_otp',
                    client_id: 'administration',
                    scopes: 'write'
                },{
                    baseURL: Context.api.apiPath,
                }).then((response) => {
                        const auth = this.loginService.setBearerAuthentication({
                            access: response.data.access_token,
                            refresh: response.data.refresh_token,
                            expiry: response.data.expires_in,
                        });
                        window.localStorage.setItem('redirectFromLogin', 'true');
                        this.handleLoginSuccess();
                        return auth;
                    clearInterval(this.interval);
                    clearInterval(this.resendInterval);
                    this.$emit('is-not-loading');
                })
                .catch((response) => {
                    this.otp = '';

                    this.handleLoginError(response);
                    this.$emit('is-not-loading');
                });
        },

        handleLoginSuccess() {
            clearInterval(this.interval);
            clearInterval(this.resendInterval);
            this.$emit('login-success');

            const animationPromise = new Promise((resolve) => {
                setTimeout(resolve, 150);
            });

            if (this.licenseViolationService) {
                this.licenseViolationService.removeTimeFromLocalStorage(this.licenseViolationService.key.showViolationsKey);
            }

            return animationPromise.then(() => {
                this.$parent.isLoginSuccess = false;
                window.sessionStorage.removeItem('loginUserDiv');
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

        handleLoginError(response) {
            this.$emit('login-error');
            setTimeout(() => {
                this.$emit('login-error');
            }, 500);

            this.createNotificationFromResponse(response);
        },

        resendOtpWithEmail(){
            clearInterval(this.interval);
            clearInterval(this.resendInterval);
            this.timer = 30;
            this.$emit('is-loading');
            Application.getContainer('init').httpClient
                .post('/backend/login/generateotp', {
                    username: this.username
                },{
                    baseURL: Context.api.apiPath,
                }).then((response) => {
                    this.$emit('is-not-loading');
                    if(response.data.type === 'success'){
                        document.getElementById('resendOtpBtn').style.display = 'none';

                        let timer2 = this.timer;
                        let countDownElementInnerHTLM = 'Resend OTP only after ' + timer2 + ' seconds';
                        document.getElementById('countDownId').innerText = countDownElementInnerHTLM;

                        this.resendInterval = setInterval(function (){
                            let seconds = parseInt(timer2 % 60, 10);

                            --seconds;
                            let displaySeconds = seconds < 10 ? "0" + seconds : seconds;
                            if(seconds < 0){
                                clearInterval(this.resendInterval);
                                clearInterval(this.interval);
                                document.getElementById('countDownId').innerText = "";
                                document.getElementById('resendOtpBtn').style.display = 'inline-block';
                            }else{
                                let countDownElementInnerHTLM = 'Resend OTP only after ' + displaySeconds + ' seconds';
                                document.getElementById('countDownId').innerText = countDownElementInnerHTLM;
                                timer2 = seconds;
                                window.sessionStorage.setItem('timerEnd',timer2);
                                document.getElementById('resendOtpBtn').style.display = 'none';
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

        backToLoginPage(){
            this.loginUserDiv = true;
            this.loginOtpDiv = false;
            localStorage.setItem('loginUserDiv','true');
            window.sessionStorage.setItem('loginUserDiv','true');
            this.username = window.sessionStorage.getItem('username');
            clearInterval(this.interval);
            clearInterval(this.resendInterval);
        },

        startTimerAfterLoading(){

            this.username = window.sessionStorage.getItem('username');
            let timer2 = this.timer;

            this.interval = setInterval(function (){
                let seconds = parseInt(timer2 % 60, 10);

                --seconds;
                let displaySeconds = seconds < 10 ? "0" + seconds : seconds;
                if (seconds < 0){
                    clearInterval(this.interval);
                    document.getElementById('countDownId').innerText = "";
                    document.getElementById('resendOtpBtn').style.display = 'inline-block';
                }else {
                    let countDownElementInnerHTLM = 'Resend OTP only after ' + displaySeconds + ' seconds';
                    document.getElementById('countDownId').innerText = countDownElementInnerHTLM;
                    document.getElementById('resendOtpBtn').style.display = 'none';
                    timer2 = seconds;
                    window.sessionStorage.setItem('timerEnd',timer2);
                }
            },1000);
        }
    },

});
