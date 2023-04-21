/*
 * @package inventory
 */
import template from './sw-blog-detail.html.twig';

const { Component, Context, Mixin, Data: { Criteria } } = Shopware;
const { EntityCollection } = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
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
            blog: [],
            isLoading: false,
            isSaveSuccessful: false,
            products:null,
            categories:null,
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

        categoryRepository() {
            return this.repositoryFactory.create('ict_blog_category');
        },

        productRepository(){
            return this.repositoryFactory.create('product');
        },

        blogCategoryCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('active', 1));
            return criteria;
        },

        productCriteria(){
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('active',1));
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
            this.products = new EntityCollection(
                this.productRepository.route,
                this.productRepository.entityName,
                Context.api,
            );

            this.categories = new EntityCollection(
                this.categoryRepository.route,
                this.categoryRepository.entityName,
                Context.api,
            )
            // Shopware.ExtensionAPI.publishData({
            //     id: 'sw-blog-detail__blog',
            //     path: 'blog',
            //     scope: this,
            // });

            if (this.blogId) {
                this.loadEntityData();
                return;
            }

            const criteria = new Criteria(1, 25);
            criteria.setIds(this.productIds);
            criteria.addFilter(Criteria.equals('active',1));

            return this.productRepository.search(criteria, Context.api).then((products) => {
                this.products = products;
            });

            const criteriaCategory = new Criteria(1, 25);
            criteriaCategory.addFilter(Criteria.equals('active',1));

            return this.categoryRepository.search(criteriaCategory, Context.api).then((categories) => {
                this.categories = categories;
            });

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

        onSave() {
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

                this.loadEntityData().then(r =>{

                } );
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
            // this.productIds = products.getIds();
            this.products = products;
            this.blog.productIds = products.getIds();
            this.blog.products = products;
        },

        setCategoryIds(categories){
            // this.categoryIds = categories.getIds();
            this.categories = categories;
            this.blog.categoryIds = categories.getIds();
            this.blog.ictBlogCategories = categories;
        }
    },
});
