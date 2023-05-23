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
            isLoading: false,
            importProduct: null,
            totalProduct: null,
            importProductMessage: null,
            offSet: 0

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
            data.append('offSet',this.offSet);

            return this.configService.httpClient.post('/_action/migration/mainproduct', data, {headers})
                .then((response) => {
                    this.isLoading = false;
                    let data = response.data;
                    if(data.type === 'Pending'){
                        // offSet++;
                        this.offSet++;
                        this.importProduct = data.importProduct;
                        this.totalProduct = data.totalProduct;
                        this.importProductMessage =this.importProduct +' import From total '+ this.totalProduct+' Products';
                        this.importMainProduct(this.offSet);
                    }else{
                        this.createNotificationSuccess({
                            title: response.data.type,
                            message: response.data.message
                        });
                    }
                    // this.createNotificationSuccess({
                    //     title: response.data.type,
                    //     message: response.data.message
                    // });

                })
                .catch((exception) => {
                    this.isLoading = false;
                });
        },
    }
})
