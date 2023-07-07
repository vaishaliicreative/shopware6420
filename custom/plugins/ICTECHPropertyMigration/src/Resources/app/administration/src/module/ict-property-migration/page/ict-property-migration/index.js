import template from './ict-property-migration.html.twig';
import './ict-property-migration.scss';

const { Component, Mixin } = Shopware;

Component.register('ict-property-migration', {
    template,

    inject: [
        'configService'
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            isLoading: false,
            isPropertyGroupLoading: false,
            isPropertyOptionLoading: false,
            isVariantLoading: false,
            importPropertyGroupCount: null,
            totalPropertyGroup: null,
            importPropertyGroupMessage: null,
            importPropertyOptionMessage: null,
            importPropertyOptionCount: null,
            totalPropertyOption: null,
            importVariantCount: null,
            totalVariant: null,
            importVariantMessage: null
        }
    },

    methods: {
        importPropertyGroups(){
            this.isPropertyGroupLoading = true;

            let headers = this.configService.getBasicHeaders();

            let data = new FormData();
            return this.configService.httpClient.post('/_action/migration/migratepropertygroup', data, {headers})
                .then((response) => {
                    this.isLoading = false;
                    this.isPropertyGroupLoading = false;
                    let data = response.data;
                    if(data.type === 'Pending'){
                        this.importPropertyGroupCount = data.importPropertyGroupCount;
                        this.totalPropertyGroup = data.totalPropertyGroup;
                        this.importPropertyGroupMessage =this.importPropertyGroupCount +' '+ this.$tc('ict-property-migration.detail.totalImportText') +' '+ this.totalPropertyGroup+' '+this.$tc('ict-property-migration.detail.propertyGroup');
                        // this.importPropertyGroups();
                    }else{
                        this.createNotificationSuccess({
                            title: response.data.type,
                            message: response.data.message
                        });
                    }

                })
                .catch((exception) => {
                    this.isPropertyGroupLoading = false;
                });
        },

        importPropertyOptions(){
            this.isPropertyOptionLoading = true;

            let headers = this.configService.getBasicHeaders();
            let data = new FormData();
            return this.configService.httpClient.post('/_action/migration/migratepropertyoption', data, {headers})
                .then((response) => {
                    this.isLoading = false;
                    this.isPropertyOptionLoading = false;
                    let data = response.data;
                    if(data.type === 'Pending'){
                        this.importPropertyOptionCount = data.importPropertyOptionCount;
                        this.totalPropertyOption = data.totalPropertyOption;
                        this.importPropertyOptionMessage =this.importPropertyOptionCount +' '+ this.$tc('ict-property-migration.detail.totalImportText') +' '+ this.totalPropertyOption+' '+this.$tc('ict-property-migration.detail.propertyValue');
                        // this.importPropertyOptions();
                    }else{
                        this.createNotificationSuccess({
                            title: response.data.type,
                            message: response.data.message
                        });
                    }

                })
                .catch((exception) => {
                    this.isPropertyOptionLoading = false;
                });
        },

        importVariants(){
            this.isVariantLoading = true;

            let headers = this.configService.getBasicHeaders();
            let data = new FormData();
            return this.configService.httpClient.post('/_action/migration/migratevariant', data, {headers})
                .then((response) => {
                    this.isLoading = false;
                    this.isVariantLoading = false;
                    let data = response.data;
                    if(data.type === 'Pending'){
                        this.importVariantCount = data.importVariantCount;
                        this.totalVariant = data.totalVariant;
                        this.importVariantMessage =this.importVariantCount +' '+ this.$tc('ict-property-migration.detail.totalImportText') +' '+ this.totalVariant+' '+this.$tc('ict-property-migration.detail.productVariant');
                        // this.importVariants();
                    }else{
                        this.createNotificationSuccess({
                            title: response.data.type,
                            message: response.data.message
                        });
                    }

                })
                .catch((exception) => {
                    this.isVariantLoading = false;
                });
        }
    }
})
