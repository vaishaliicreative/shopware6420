/*
 * @package inventory
 */
import EntityValidationService from 'src/app/service/entity-validation.service';
import template from './sw-blog-detail.html.twig';

const { Component, Mixin, Data:{ Criteria }} = Shopware;

const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('sw-blog-detail',{
    template,

    inject:['repositoryFactory', 'acl','entityValidationService'],

    mixins:[
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification')
    ],

    shortcuts:{
        'SYSTEMKEY+S':'onSave',
        ESCAPE: 'onCancel'
    },

    props:{
        blogId:{
            type:String,
            required:false,
            default:null,
        }
    },

    date(){
        return{
            blog:[],
            category:null,
            product:[],
            isLoading: false,
            isSaveSuccessful: false,
        }
    },
    metaInfo(){
        return{
            title:this.$createTitle(this.identifier),
        }
    },

    computed:{
        identifier(){
            return this.placeholder(this.blog,'name');
        },
        blogIsLoading(){
            return this.isLoading || this.blog == null;
        },

        blogRepository(){
            return this.repositoryFactory.create('ict_blog');
        },

        productRepository(){
            return this.repositoryFactory.create('product');
        },

        productCriteria(){
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('active',1));
        },

        tooltipSave() {
            if (this.acl.can('blog.editor')) {
                const systemKey = this.$device.getSystemKey();

                return {
                    message: `${systemKey} + S`,
                    appearance: 'light',
                };
            }

            return {
                showDelay: 300,
                message: this.$tc('sw-privileges.tooltip.warning'),
                disabled: this.acl.can('blog.editor'),
                showOnDisabledElements: true,
            };
        },

        tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light',
            };
        },
        ...mapPropertyErrors('blog', ['name','releaseDate','author']),
    },
    watch:{
        blogId(){
            this.createdComponent();
        }
    },
    created() {
        this.createdComponent();
    },
    methods:{
        createdComponent(){
            Shopware.ExtensionAPI.publishData({
               id:'sw-blog-detail__blog',
               path:'blog',
                scope:this,
            });
            if(this.blogId){
                this.loadEntityData();
                return;
            }
            Shopware.State.commit('context/resetLanguageToDefault');
            this.blog = this.blogRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;

            const [blogResponse, customFieldResponse] = await Promise.allSettled([
                this.blogRepository.get(this.blogId),
            ]);

            if (blogResponse.status === 'fulfilled') {
                this.blog = blogResponse.value;
            }

            if (blogResponse.status === 'rejected' ) {
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationLoadingDataErrorMessage',
                    ),
                });
            }

            this.isLoading = false;
        },

        abortOnLanguageChange() {
            return this.blogRepository.hasChanges(this.blog);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.loadEntityData();
        },

        onCancel() {
            this.$router.push({ name: 'sw.blog.index' });
        },

        onSave(){
            if (!this.acl.can('blog.editor')) {
                return;
            }

            if (!this.entityValidationService.validate(this.blog)) {
                const titleSaveError = this.$tc('global.default.error');
                const messageSaveError = this.$tc(
                    'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid',
                );

                this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError,
                });
                return Promise.resolve();
            }

            this.isLoading = true;

            this.blogRepository.save(this.blog).then(()=>{
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if(this.blogId === null){
                    this.$router.push({ name: 'sw.blog.detail', params: { id: this.blog.id } });
                    return;
                }
            }).catch((exception)=>{
               this.isLoading = false;
               this.createNotificationError({
                   message: this.$tc(
                       'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid',
                   )
               });
               throw exception;
            });
        }
    }
})
