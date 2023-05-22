import template from './ict-core-import-config.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('ict-core-import-config', {
    template,

    inject: [
        'repositoryFactory',
        'configService',
        'systemConfigApiService',
        'documentService'
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            isLoading: false
        }
    },
    methods: {
        propertyFunction() {
            // this.isLoading = true;
            let headers = this.configService.getBasicHeaders();

            let data = new FormData();
            data.append('formData', 'abcd');

            return this.configService.httpClient.post('/_action/migration/property', data, {headers})
                .then((response) => {
                    this.isLoading = false;

                    this.createNotificationSuccess({
                        title: response.data.type,
                        message: response.data.message
                    });

                })
                .catch((exception) => {
                    this.isLoading = false;
                });
        },

        importMainProduct(){
            let headers = this.configService.getBasicHeaders();

            let data = new FormData();
            data.append('type', 'main_product');

            return this.configService.httpClient.post('/_action/migration/mainproduct', data, {headers})
                .then((response) => {
                    this.isLoading = false;

                    this.createNotificationSuccess({
                        title: response.data.type,
                        message: response.data.message
                    });

                })
                .catch((exception) => {
                    this.isLoading = false;
                });
        }

    }
})
