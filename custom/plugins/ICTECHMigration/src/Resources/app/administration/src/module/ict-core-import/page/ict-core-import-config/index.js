import template from './ict-core-import-config.html.twig';

const {Component, Mixin} = Shopware;

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
            isProductLoading: false,
            isCategoryLoading: false,
            isVariantLoading: false,
            importProduct: null,
            totalProduct: null,
            importProductMessage: null,
            incrementalValue: null,
            importCategoryMessage: null,
            importCategoryCount: null,
            totalCategory: null,
            importVariant: null,
            totalVariant: null,
            importVariantMessage: null
        }
    },
    methods: {
        propertyFunction(value) {
            console.log(value);
            // this.isLoading = true;
            let headers = this.configService.getBasicHeaders();

            let data = new FormData();
            data.append('startingValue', value);

            return this.configService.httpClient.post('/_action/migration/property', data, {headers})
                .then((response) => {
                    this.isLoading = false;

                    this.createNotificationSuccess({
                        title: response.data.type,
                        message: response.data.message
                    });
                    if(response.data.type == "Pending"){
                        this.propertyFunction(response.data.message);
                    }
                    if(response.data.type == "Success"){
                        this.incrementalValue = response.data.message;
                    }

                })
                .catch((exception) => {
                    this.isLoading = false;
                });
        },

        importMainProduct(){
            this.isProductLoading = true;
            let headers = this.configService.getBasicHeaders();

            let data = new FormData();
            data.append('type', 'main_product');
            // data.append('offSet',this.offSet);

            return this.configService.httpClient.post('/_action/migration/mainproduct', data, {headers})
                .then((response) => {
                    this.isLoading = false;
                    this.isProductLoading = false;
                    let data = response.data;
                    if(data.type === 'Pending'){
                        // offSet++;
                        this.importProduct = data.importProduct;
                        this.totalProduct = data.totalProduct;
                        this.importProductMessage =this.importProduct +' import From total '+ this.totalProduct+' Products';
                        // return;
                        this.importMainProduct();
                    }else{
                        this.createNotificationSuccess({
                            title: response.data.type,
                            message: response.data.message
                        });
                    }
                })
                .catch((exception) => {
                    this.isLoading = false;
                });
        },

        importCategory(){
            this.isCategoryLoading = true;
            let headers = this.configService.getBasicHeaders();

            let data = new FormData();
            data.append('type', 'category');

            return this.configService.httpClient.post('/_action/migration/addcategory', data, {headers})
                .then((response) => {
                    this.isLoading = false;
                    this.isCategoryLoading = false;
                    let data = response.data;
                    if(data.type === 'Pending'){
                        this.importCategoryCount = data.importCategoryCount;
                        this.totalCategory = data.totalCategory;
                        this.importCategoryMessage =this.importCategoryCount +' import From total '+ this.totalCategory+' Categories';
                        // return;
                        this.importCategory();
                    }else{
                        this.createNotificationSuccess({
                            title: response.data.type,
                            message: response.data.message
                        });
                    }
                })
                .catch((exception) => {
                    this.isLoading = false;
                });
        },

        importVariantProduct(){
            this.isVariantLoading = true;
            let headers = this.configService.getBasicHeaders();

            let data = new FormData();
            data.append('type', 'variant product');

            return this.configService.httpClient.post('/_action/migration/addvariantproduct', data, {headers})
                .then((response) => {
                    this.isLoading = false;
                    this.isVariantLoading = false;
                    let data = response.data;
                    if(data.type === 'Pending'){;
                        this.importVariant = data.importVariant;
                        this.totalVariant = data.totalVariant;
                        this.importVariantMessage =this.importVariant +' import From total '+ this.totalVariant+' Variant Product';
                        // return;
                        this.importVariantProduct();
                    }else{
                        this.variantOffSet = 0;
                        this.createNotificationSuccess({
                            title: response.data.type,
                            message: response.data.message
                        });
                    }
                })
                .catch((exception) => {
                    this.isLoading = false;
                });
        }
    }
})
