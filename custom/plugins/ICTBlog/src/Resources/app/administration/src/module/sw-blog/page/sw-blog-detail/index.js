/*
 * @package inventory
 */
import EntityValidationService from 'src/app/service/entity-validation.service';
import template from './sw-blog-detail.html.twig';
const { Component,Context, Mixin, Data: { Criteria } } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();
const { EntityCollection } = Shopware.Data;
Component.register('sw-blog-detail', {
    template,

    inject: ['repositoryFactory', 'acl','entityValidationService'],

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    props: {
        blogId: {
            type: String,
            required: false,
            default: null,
        },
    },
    data() {
        return {
            blog: null,
            isLoading: false,
            isSaveSuccessful: false,
            blogCategories: null,
            blogProducts:null,
        };
    },
    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },
    computed: {
        identifier() {
            return this.placeholder(this.blog, 'name');
        },

        blogIsLoading() {
            return this.isLoading || this.blog == null;
        },

        blogRepository() {
            return this.repositoryFactory.create('ict_blog');
        },

        blogCategoryRepository(){
            return this.repositoryFactory.create('ict_blog_category');
        },

        productRepository(){
            return this.repositoryFactory.create('product');
        },

        productCriteria(){
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('active',1));
            criteria.addFilter(Criteria.equals('parent_id','NULL'))
            return criteria;
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

        blogCategoryCriteria(){
            const criteria = new Criteria(1,25);
            criteria.addFilter(Criteria.equals('active',1));
            return criteria
        },

        ...mapPropertyErrors('blog', ['name','author','releaseDate']),
    },

    watch: {
        blogId() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {

            this.blogCategories = new EntityCollection(
                this.blogCategoryRepository.route,
                this.blogCategoryRepository.entityName,
                Context.api,
            );

            this.blogProducts = new EntityCollection(
                this.productRepository.route,
                this.productRepository.entityName,
                Context.api,
            );

            Shopware.ExtensionAPI.publishData({
                id: 'sw-blog-detail__blog',
                path: 'blog',
                scope: this,
            });


            if (this.blogId) {
                this.loadEntityData();
                return;
            }

            Shopware.State.commit('context/resetLanguageToDefault');
            this.blog = this.blogRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;
            const blogCriteria = new Criteria();
            blogCriteria.addFilter(Criteria.equals('id',this.blogId))
            blogCriteria.addAssociation('ictBlogCategories');
            blogCriteria.addAssociation('products');
            this.blogRepository.search(blogCriteria,Context.api).then((res)=>{
                // console.log(res)
                this.blog = res[0];
                // console.log(this.blog)
                this.blogCategories = this.blog.ictBlogCategories;
                this.blogProducts = this.blog.products;
                // console.log(this.blogProducts);
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationLoadingDataErrorMessage',
                    ),
                });
                throw exception;
            });

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

        onSave() {
            console.log(this.blog);
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

            this.blogRepository.save(this.blog).then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if (this.blogId === null) {
                    this.$router.push({ name: 'sw.blog.detail', params: { id: this.blog.id } });
                    return;
                }

                this.loadEntityData();
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid',
                    ),
                });
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'sw.blog.index' });
        },

        setProductIds(products) {
            this.blogProducts = products;
            this.blog.productIds = products.getIds();
            this.blog.products = products;
        },

        setCategoryIds(categories){
            this.blogCategories = categories;
            this.blog.categoryIds = categories.getIds();
            this.blog.ictBlogCategories = categories;
        }
    },
});
