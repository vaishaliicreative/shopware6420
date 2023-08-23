!function(e){var n={};function t(s){if(n[s])return n[s].exports;var i=n[s]={i:s,l:!1,exports:{}};return e[s].call(i.exports,i,i.exports,t),i.l=!0,i.exports}t.m=e,t.c=n,t.d=function(e,n,s){t.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:s})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,n){if(1&n&&(e=t(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var s=Object.create(null);if(t.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var i in e)t.d(s,i,function(n){return e[n]}.bind(null,i));return s},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p="/bundles/ictechrestockreminder/",t(t.s="+p43")}({"+p43":function(e,n,t){"use strict";t.r(n);Shopware.Component.register("install-cron-time-interval-detail",{template:'{% block sw_settings_address_index %}\n    <sw-page class="sw-settings-address">\n        {% block sw_settings_address_search_bar %}\n            <template slot="search-bar">\n                <sw-search-bar />\n            </template>\n        {% endblock %}\n        {% block sw_settings_address_smart_bar_header %}\n            <template slot="smart-bar-header">\n                {% block sw_settings_address_smart_bar_header_title %}\n                        {% block sw_settings_address_smart_bar_header_title_text %}\n                            <h2>\n                                {{ $tc(\'sw-settings.index.title\') }}\n                                <sw-icon\n                                    name="regular-chevron-right-xs"\n                                    small\n                                />\n                                {{ $tc(\'install-cron-time-interval.title\') }}\n                            </h2>\n                        {% endblock %}\n                    </h2>\n                {% endblock %}\n            </template>\n        {% endblock %}\n\n        {% block sw_settings_address_smart_bar_actions %}\n            <template slot="smart-bar-actions">\n                {% block sw_settings_address_actions_save %}\n                    <sw-button-process\n                        class="sw-settings-address__save-action"\n                        :is-loading="isLoading"\n                        :process-success="isSaveSuccessful"\n                        :disabled="isLoading"\n                        variant="primary"\n                        @process-finish="saveFinish"\n                        @click="onSave"\n                    >\n                        {{ $tc(\'sw-settings-address.general.buttonSave\') }}\n                    </sw-button-process>\n                {% endblock %}\n            </template>\n        {% endblock %}\n        {% block sw_settings_address_content %}\n            <template slot="content">\n                <sw-card-view>\n                    <sw-skeleton v-if="isLoading" />\n                    <sw-system-config\n                        v-show="!isLoading"\n                        ref="systemConfig"\n                        domain="ICTECHRestockReminder.restock"\n                        @loading-changed="onLoadingChanged"\n                    />\n                </sw-card-view>\n            </template>\n        {% endblock %}\n    </sw-page>\n{% endblock %}\n',data:function(){return{isLoading:!1,isSaveSuccessful:!1}},created:function(){this.createdComponent()},methods:{saveFinish:function(){this.isSaveSuccessful=!1},onSave:function(){var e=this;this.isSaveSuccessful=!1,this.isLoading=!0,this.$refs.systemConfig.saveAll().then((function(){e.isLoading=!1,e.isSaveSuccessful=!0})).catch((function(n){e.isLoading=!1,e.createNotificationError({message:n})}))},onLoadingChanged:function(e){this.isLoading=e},openModal:function(){this.open=!0},closeModal:function(){this.open=!1},onCancel:function(){this.$router.push({name:"sw.settings.index.system"})}}}),Shopware.Module.register("install-cron-time-interval",{type:"plugin",name:"install-cron-time-interval.name",title:"install-cron-time-interval.title",description:"install-cron-time-interval.description",color:"#9AA8B5",icon:"default-time-clock",favicon:"icon-module-settings.png",entity:"scheduled_task",routes:{index:{components:{default:"install-cron-time-interval-detail"},path:"index",meta:{parentPath:"sw.settings.index.plugins"}}},settingsItem:{privilege:"system.system_config",to:"install.cron.time.interval.index",group:"plugins",icon:"default-time-clock"}})}});