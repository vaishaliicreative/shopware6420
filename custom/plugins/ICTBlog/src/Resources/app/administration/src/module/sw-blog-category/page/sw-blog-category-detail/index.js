/*
 * @package inventory
 */
import EntityValidationService from 'src/app/service/entity-validation.service';
import template from './sw-blog-category-detail.html.twig';

const { Component, Mixin, Data: { Criteria } } = Shopware;

const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('sw-blog-category-detail', {
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
        blogCategoryId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            blogCategory: [],
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        identifier() {
            return this.placeholder(this.blogCategory, 'name');
        },

        blogCategoryIsLoading() {
            return this.isLoading || this.blogCategory == null;
        },

        blogCategoryRepository() {
            return this.repositoryFactory.create('ict_blog_category');
        },

        tooltipSave() {
            if (this.acl.can('blog_category.editor')) {
                const systemKey = this.$device.getSystemKey();

                return {
                    message: `${systemKey} + S`,
                    appearance: 'light',
                };
            }

            return {
                showDelay: 300,
                message: this.$tc('sw-privileges.tooltip.warning'),
                disabled: this.acl.can('order.editor'),
                showOnDisabledElements: true,
            };
        },

        tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light',
            };
        },

        hasStates(){
            return this.state.length > 0;
        },

        ...mapPropertyErrors('blogCategory', ['name','city']),
    },

    watch: {
        blogCategoryId() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            Shopware.ExtensionAPI.publishData({
                id: 'sw-blog-category-detail__blog_category',
                path: 'blog-category',
                scope: this,
            });
            if (this.blogCategoryId) {
                this.loadEntityData();
                return;
            }

            Shopware.State.commit('context/resetLanguageToDefault');
            this.blogCategory = this.blogCategoryRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;

            const [blogCategoryResponse, customFieldResponse] = await Promise.allSettled([
                this.blogCategoryRepository.get(this.blogCategoryId),
            ]);

            if (blogCategoryResponse.status === 'fulfilled') {
                this.blogCategory = blogCategoryResponse.value;
            }

            if (blogCategoryResponse.status === 'rejected' ) {
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationLoadingDataErrorMessage',
                    ),
                });
            }

            this.isLoading = false;
        },

        abortOnLanguageChange() {
            return this.blogCategoryRepository.hasChanges(this.blogCategory);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.loadEntityData();
        },

        onSave() {
            if (!this.acl.can('blog_category.editor')) {
                return;
            }

            if (!this.entityValidationService.validate(this.blogCategory)) {
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

            this.blogCategoryRepository.save(this.blogCategory).then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if (this.blogCategoryId === null) {
                    this.$router.push({ name: 'sw.blog.category.detail', params: { id: this.blogCategory.id } });
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
            this.$router.push({ name: 'sw.blog.category.index' });
        },
    },
});
