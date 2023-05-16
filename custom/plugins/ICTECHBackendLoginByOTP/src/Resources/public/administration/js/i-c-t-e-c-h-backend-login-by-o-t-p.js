!function(e){var n={};function t(i){if(n[i])return n[i].exports;var o=n[i]={i:i,l:!1,exports:{}};return e[i].call(o.exports,o,o.exports,t),o.l=!0,o.exports}t.m=e,t.c=n,t.d=function(e,n,i){t.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:i})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,n){if(1&n&&(e=t(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(t.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var o in e)t.d(i,o,function(n){return e[n]}.bind(null,o));return i},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p="/bundles/ictechbackendloginbyotp/",t(t.s="OW7Z")}({FTU5:function(e){e.exports=JSON.parse('{"sw-login":{"index":{"buttonSendOTP":"Send Otp"},"verify":{"otpHeadlineForm":"Enter Otp","labelOtp":"OTP","placeholderOtp":"Enter your OTP...","buttonResendOTP":"Resend"},"detail":{"pluginErrorTitle":"Error","pluginErrorMessage":"Something went wrong","pluginNotFoundMessage":"Invalid User name","pluginNotFoundOtpMessage":"Invalid OTP"}}}')},HeCZ:function(e){e.exports=JSON.parse("{}")},OW7Z:function(e,n,t){"use strict";t.r(n);var i=t("FTU5"),o=t("HeCZ"),s=Shopware,r=s.Component,l=s.Mixin,a=s.Context,c=s.Application;r.override("sw-login-login",{template:'{% block sw_login_login %}\n    {% block sw_login_login_username %}\n        <div v-if="loginUserDiv">\n            <form\n                class="sw-login-login"\n                @submit.prevent="createUserOtpWithEmail"\n            >\n                {% block sw_login_login_form_headline %}\n                    <h2 class="sw-login__content-headline">\n                        {{ $tc(\'sw-login.index.headlineForm\') }}\n                    </h2>\n                {% endblock %}\n\n                {% block sw_login_login_alert %}\n                    <sw-alert\n                        v-if="showLoginAlert"\n                        variant="info"\n                        appearance="default"\n                        :show-icon="true"\n                        :closable="false"\n                    >\n                        {{ loginAlertMessage }}\n                    </sw-alert>\n                {% endblock %}\n\n                {% block sw_login_login_user_field %}\n                    <sw-text-field\n                        v-model="username"\n                        v-autofocus\n                        :label="$tc(\'sw-login.index.labelUsername\')"\n                        :placeholder="$tc(\'sw-login.index.placeholderUsername\')"\n                        :disabled="showLoginAlert"\n                        required\n                    />\n                {% endblock %}\n\n                {% block sw_login_login_password_field %}\n\n                {% endblock %}\n\n                {% block sw_login_login_submit %}\n                    <div class="sw-login__submit">\n                        {% block sw_login_login_forgot_password %}\n                            <router-link\n                                :to="{ name: \'sw.login.index.recovery\' }"\n                                class="sw-login__forgot-password-action"\n                            >\n                                {{ $tc(\'sw-login.index.forgottenPasswordLink\') }}\n                            </router-link>\n\n                        {% endblock %}\n                        {% block sw_login_login_submit_button %}\n                            <sw-button\n                                :disabled="username.length <= 0 || showLoginAlert"\n                                class="sw-login__login-action"\n                                variant="primary"\n                            >\n                                {{ $tc(\'sw-login.index.buttonSendOTP\') }}\n                            </sw-button>\n                        {% endblock %}\n                    </div>\n                {% endblock %}\n            </form>\n        </div>\n\n        <div v-else-if="loginOtpDiv">\n            <form  class="sw-login-login"\n                   @submit.prevent="verifyOtpWithEmail"\n                   >\n                {% block sw_login_verify_form_headline %}\n                    <h2 class="sw-login__content-headline">\n                            <sw-icon\n                                class="sw-login__back-arrow"\n                                name="regular-long-arrow-left"\n                                small\n                                @click="backToLoginPage"\n                            />\n                        {{ $tc(\'sw-login.verify.otpHeadlineForm\') }}\n                    </h2>\n                {% endblock %}\n\n                {% block sw_login_verify_alert %}\n                    <sw-alert\n                        v-if="showLoginAlert"\n                        variant="info"\n                        appearance="default"\n                        :show-icon="true"\n                        :closable="false"\n                    >\n                        {{ loginAlertMessage }}\n                    </sw-alert>\n                {% endblock %}\n\n                {% block sw_login_verify_otp_field %}\n                    <sw-text-field\n                        v-model="otp"\n                        v-autofocus\n                        :label="$tc(\'sw-login.verify.labelOtp\')"\n                        :placeholder="$tc(\'sw-login.verify.placeholderOtp\')"\n                        :disabled="showLoginAlert"\n                        required\n                    />\n                {% endblock %}\n                {% block sw_login_verify_submit %}\n                    <div class="sw-login__submit">\n                        {% block sw_login_verify_resend_otp %}\n                            <div style="display: inline-block">\n                                <div id="countDownId" class="countdown text-danger" style="display: inline-block;color:red">\n                                    Resend OTP only after {{ timer }}  seconds\n                                </div>\n                                <a\n                                    @click.prevent="resendOtpWithEmail"\n                                    class="sw-button sw-button--primary sw-login__verify-action resendOtp"\n                                    variant="primary"\n                                    id="resendOtpBtn"\n                                    style="display: none"\n                                >\n                                    {{ $tc(\'sw-login.verify.buttonResendOTP\') }}\n                                </a>\n                            </div>\n\n                        {% endblock %}\n                        {% block sw_login_verify_submit_button %}\n                            <sw-button\n                                :disabled="otp.length <= 0 || showLoginAlert"\n                                class="sw-login__login-action"\n                                variant="primary"\n\n                            >\n                                {{ $tc(\'sw-login.index.buttonLogin\') }}\n                            </sw-button>\n\n                        {% endblock %}\n                    </div>\n                {% endblock %}\n\n            </form>\n        </div>\n\n    {% endblock %}\n\n{% endblock %}\n',inject:["configService"],mixins:[l.getByName("notification")],data:function(){return{username:"",loginAlertMessage:"",loginUserDiv:"",loginOtpDiv:"",otp:"",resendOptDiv:"",timer:30,interval:null}},computed:{showLoginAlert:function(){return"string"==typeof this.loginAlertMessage&&this.loginAlertMessage.length>=1}},created:function(){var e=window.sessionStorage.getItem("timerEnd");this.timer=null===e||0==e?30:e;var n=window.sessionStorage.getItem("loginUserDiv");null===n?(this.loginUserDiv=!0,this.loginOtpDiv=!1):"false"===n?(this.loginUserDiv=!1,this.loginOtpDiv=!0,this.startTimerAfterLoading()):(this.loginUserDiv=!0,this.loginOtpDiv=!1),localStorage.getItem("sw-admin-locale")||Shopware.State.dispatch("setAdminLocale",navigator.language)},methods:{createUserOtpWithEmail:function(){var e=this;this.$emit("is-loading"),c.getContainer("init").httpClient.post("/backend/login/generateotp",{username:this.username},{baseURL:a.api.apiPath}).then((function(n){if(e.$emit("is-not-loading"),"success"===n.data.type){e.loginUserDiv=!1,e.loginOtpDiv=!0,window.sessionStorage.setItem("loginUserDiv","false"),e.timer=30;var t=30;e.interval=setInterval((function(){var e=parseInt(t%60,10),n=--e<10?"0"+e:e;if(e<0)clearInterval(this.interval),document.getElementById("countDownId").innerText="",document.getElementById("resendOtpBtn").style.display="inline-block";else{var i="Resend OTP only after "+n+" seconds";document.getElementById("countDownId").innerText=i,document.getElementById("resendOtpBtn").style.display="none",t=e,window.sessionStorage.setItem("timerEnd",t)}}),1e3),window.sessionStorage.setItem("username",e.username)}else"notfound"===n.data.type?e.createNotificationError({title:"Error",message:e.$tc("sw-login.detail.pluginNotFoundMessage")}):e.createNotificationError({title:e.$tc("sw-login.detail.pluginErrorTitle"),message:e.$tc("sw-login.detail.pluginErrorMessage")})}))},verifyOtpWithEmail:function(){var e=this;return this.$emit("is-loading"),c.getContainer("init").httpClient.post("/backend/login/verifyotp",{username:this.username,otp:this.otp,scopes:"write",client_id:"SWIABFPRQ25QRZFYZGPLBLBJEQ",client_secret:"SnhnQXVBM2tBWkFSSVNVc3MxcnZvcjVKTVB5cklDU3l4N1E5NUo",grant_type:"client_credentials"},{baseURL:a.api.apiPath}).then((function(n){if("success"===n.data.type){e.loginService.setBearerAuthentication({access:n.data.access_token,expiry:n.data.expires_in});window.localStorage.setItem("redirectFromLogin","true"),e.handleLoginSuccess()}else"notfound"===n.data.type?e.createNotificationError({title:"Error",message:e.$tc("sw-login.detail.pluginNotFoundOtpMessage")}):e.createNotificationError({title:e.$tc("sw-login.detail.pluginErrorTitle"),message:e.$tc("sw-login.detail.pluginErrorMessage")});e.$emit("is-not-loading")})).catch((function(n){e.otp="",e.handleLoginError(n),e.$emit("is-not-loading")}))},handleLoginSuccess:function(){var e=this;clearInterval(this.interval),this.$emit("login-success");var n=new Promise((function(e){setTimeout(e,150)}));return this.licenseViolationService&&this.licenseViolationService.removeTimeFromLocalStorage(this.licenseViolationService.key.showViolationsKey),n.then((function(){e.$parent.isLoginSuccess=!1,window.sessionStorage.removeItem("loginUserDiv"),window.sessionStorage.removeItem("timerEnd"),e.forwardLogin(),sessionStorage.getItem("sw-login-should-reload")&&(sessionStorage.removeItem("sw-login-should-reload"),window.location.reload(!0))}))},handleLoginError:function(e){var n=this;this.$emit("login-error"),setTimeout((function(){n.$emit("login-error")}),500),this.createNotificationFromResponse(e)},resendOtpWithEmail:function(){var e=this;console.log("click resend btn"),clearInterval(this.interval),this.timer=30,this.$emit("is-loading"),c.getContainer("init").httpClient.post("/backend/login/generateotp",{username:this.username},{baseURL:a.api.apiPath}).then((function(n){if(e.$emit("is-not-loading"),"success"===n.data.type){document.getElementById("resendOtpBtn").style.display="none";var t=e.timer,i="Resend OTP only after "+t+" seconds";document.getElementById("countDownId").innerText=i;var o=setInterval((function(){var e=parseInt(t%60,10),n=--e<10?"0"+e:e;if(e<0)clearInterval(o),document.getElementById("countDownId").innerText="",document.getElementById("resendOtpBtn").style.display="inline-block";else{var i="Resend OTP only after "+n+" seconds";document.getElementById("countDownId").innerText=i,t=e,window.sessionStorage.setItem("timerEnd",t)}}),1e3)}else"notfound"===n.data.type?e.createNotificationError({title:"Error",message:e.$tc("sw-login.detail.pluginNotFoundMessage")}):e.createNotificationError({title:e.$tc("sw-login.detail.pluginErrorTitle"),message:e.$tc("sw-login.detail.pluginErrorMessage")})}))},backToLoginPage:function(){this.loginUserDiv=!0,this.loginOtpDiv=!1,localStorage.setItem("loginUserDiv","true"),window.sessionStorage.setItem("loginUserDiv","true"),this.username=window.sessionStorage.getItem("username"),clearInterval(this.interval)},startTimerAfterLoading:function(){this.username=window.sessionStorage.getItem("username");var e=this.timer;this.interval=setInterval((function(){var n=parseInt(e%60,10),t=--n<10?"0"+n:n;if(n<0)clearInterval(this.interval),document.getElementById("countDownId").innerText="",document.getElementById("resendOtpBtn").style.display="inline-block";else{var i="Resend OTP only after "+t+" seconds";document.getElementById("countDownId").innerText=i,document.getElementById("resendOtpBtn").style.display="none",e=n,window.sessionStorage.setItem("timerEnd",e)}}),1e3)}}});var d=Shopware,g=d.Component,u=d.Mixin,m=d.Application,p=d.Context;g.register("sw-login-verify",{template:'{% block sw_login_verify %}\n    <form  class="sw-login-login"\n           @submit.prevent="verifyOtpWithEmail">\n        {% block sw_login_login_form_headline %}\n            <h2 class="sw-login__content-headline">\n                <router-link\n                    class="sw-login__back"\n                    :to="{ name: \'sw.login.index.login\' }"\n                >\n                    <sw-icon\n                        class="sw-login__back-arrow"\n                        name="regular-long-arrow-left"\n                        small\n                    />\n                </router-link>\n                {{ $tc(\'sw-login.verify.otpHeadlineForm\') }}\n            </h2>\n        {% endblock %}\n\n        {% block sw_login_verify_alert %}\n            <sw-alert\n                v-if="showLoginAlert"\n                variant="info"\n                appearance="default"\n                :show-icon="true"\n                :closable="false"\n            >\n                {{ loginAlertMessage }}\n            </sw-alert>\n        {% endblock %}\n\n        {% block sw_login_verify_otp_field %}\n            <sw-text-field\n                v-model="otp"\n                v-autofocus\n                :label="$tc(\'sw-login.verify.labelOtp\')"\n                :placeholder="$tc(\'sw-login.verify.placeholderOtp\')"\n                :disabled="showLoginAlert"\n                required\n            />\n        {% endblock %}\n        {% block sw_login_verify_submit %}\n            <div class="sw-login__submit">\n                {% block sw_login_verify_resend_otp %}\n                    <div style="display: inline-block">\n                        <div id="countDownId" class="countdown" style="display: inline-block;color:red">\n                            Resend OTP only after {{ timer }}  seconds\n                        </div>\n                        <a\n                            @click.prevent="resendOtpWithEmail"\n                            class="sw-button sw-button--primary sw-login__verify-action resendOtp"\n                            variant="primary"\n                            id="resendOtpBtn"\n                            style="display: none"\n                        >\n                            {{ $tc(\'sw-login.verify.buttonResendOTP\') }}\n                        </a>\n                    </div>\n                {% endblock %}\n                {% block sw_login_verify_submit_button %}\n                    <sw-button\n                        :disabled="otp.length <= 0 || showLoginAlert"\n                        class="sw-login__login-action"\n                        variant="primary"\n                    >\n                        {{ $tc(\'sw-login.index.buttonLogin\') }}\n                    </sw-button>\n                {% endblock %}\n            </div>\n\n        {% endblock %}\n\n    </form>\n{% endblock %}\n',inject:["licenseViolationService","loginService"],mixins:[u.getByName("notification")],data:function(){return{username:"",loginAlertMessage:"",otp:"",timer:30,interval:null}},computed:{showLoginAlert:function(){return"string"==typeof this.loginAlertMessage&&this.loginAlertMessage.length>=1}},created:function(){this.username=window.sessionStorage.getItem("username"),localStorage.getItem("sw-admin-locale")||Shopware.State.dispatch("setAdminLocale",navigator.language),this.$emit("is-not-loading");var e=window.sessionStorage.getItem("timerEnd");this.timer=0==e?30:e,this.createdComponent()},methods:{createdComponent:function(){var e=this.timer;this.interval=setInterval((function(){var n=parseInt(time2%60,10),t=--n<10?"0"+n:n;if(n<0)clearInterval(this.interval),document.getElementById("countDownId").innerText="",document.getElementById("resendOtpBtn").style.display="inline-block";else{var i="Resend OTP only after "+t+" seconds";document.getElementById("countDownId").innerText=i,document.getElementById("resendOtpBtn").style.display="none",e=n,window.sessionStorage.setItem("timerEnd",e)}}),1e3)},verifyOtpWithEmail:function(){var e=this;return this.$emit("is-loading"),m.getContainer("init").httpClient.post("/backend/login/verifyotp",{username:this.username,otp:this.otp,grant_type:"password",client_id:"administration",scopes:"write"},{baseURL:p.api.apiPath}).then((function(n){if("success"===n.data.type){var t=e.loginService.setBearerAuthentication({access:n.data.access_token,refresh:n.data.refresh_token,expiry:n.data.expires_in});return e.handleLoginSuccess(),t}"notfound"===n.data.type?e.createNotificationError({title:"Error",message:e.$tc("sw-login.detail.pluginNotFoundOtpMessage")}):e.createNotificationError({title:e.$tc("sw-login.detail.pluginErrorTitle"),message:e.$tc("sw-login.detail.pluginErrorMessage")}),e.$emit("is-not-loading")})).catch((function(n){e.otp="",e.handleLoginError(n),e.$emit("is-not-loading")}))},handleLoginSuccess:function(){var e=this;this.otp="",this.$emit("login-success");var n=new Promise((function(e){setTimeout(e,150)}));return this.licenseViolationService&&this.licenseViolationService.removeTimeFromLocalStorage(this.licenseViolationService.key.showViolationsKey),n.then((function(){e.$parent.isLoginSuccess=!1,window.sessionStorage.removeItem("timerEnd"),e.forwardLogin(),sessionStorage.getItem("sw-login-should-reload")&&(sessionStorage.removeItem("sw-login-should-reload"),window.location.reload(!0))}))},forwardLogin:function(){var e=JSON.parse(sessionStorage.getItem("sw-admin-previous-route"));sessionStorage.removeItem("sw-admin-previous-route"),!Shopware.Context.app.firstRunWizard||this.$router.history.current.name.startsWith("sw.first.run.wizard.")?null!=e&&e.fullPath?this.$router.push(e.fullPath):this.$router.push({name:"core"}):this.$router.push({name:"sw.first.run.wizard.index"})},handleLoginError:function(e){this.otp="",this.$super("handleLoginError",e)},resendOtpWithEmail:function(){var e=this;console.log("click resend btn"),this.timer=30,clearInterval(this.interval),m.getContainer("init").httpClient.post("/backend/login/generateotp",{username:this.username},{baseURL:p.api.apiPath}).then((function(n){if("success"===n.data.type){document.getElementById("resendOtpBtn").style.display="none";var t=e.timer,i=setInterval((function(){var e=parseInt(t%60,10),n=--e<10?"0"+e:e;if(e<0)clearInterval(i),document.getElementById("countDownId").innerText="",document.getElementById("resendOtpBtn").style.display="inline-block";else{var o="Resend OTP only after "+n+" seconds";document.getElementById("countDownId").innerText=o,t=e,localStorage.setItem("timerEnd",t)}}),1e3)}else"notfound"===n.data.type?e.createNotificationError({title:"Error",message:e.$tc("sw-login.detail.pluginNotFoundMessage")}):e.createNotificationError({title:e.$tc("sw-login.detail.pluginErrorTitle"),message:e.$tc("sw-login.detail.pluginErrorMessage")})}))}}}),Shopware.Locale.extend("en-GB",i),Shopware.Locale.extend("de-DE",o),console.log("call main js")}});