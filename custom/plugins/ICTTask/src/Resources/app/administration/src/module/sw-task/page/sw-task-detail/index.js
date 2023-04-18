/*
 * @package inventory
 */
import EntityValidationService from 'src/app/service/entity-validation.service';
import template from './sw-task-detail.html.twig';
import './sw-task-detail.scss';

const { Component, Mixin, Data: { Criteria } } = Shopware;

const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Component.register('sw-task-detail', {
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
        taskId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            task: [],
            country: null,
            state: [],
            product:[],
            media:[],
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
            return this.placeholder(this.task, 'name');
        },

        taskIsLoading() {
            return this.isLoading || this.task == null;
        },

        taskRepository() {
            return this.repositoryFactory.create('ict_task');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        countryRepository(){
            return this.repositoryFactory.create('country');
        },

        countryStateRepository(){
            return this.repositoryFactory.create('country_state');
        },

        productRepository(){
            return this.repositoryFactory.create('product');
        },

        mediaUploadTag() {
            return `sw-task-detail--${this.task.id}`;
        },

        countryId: {
            get() {
                return this.task.countryId;
            },

            set(countryId) {
                this.task.countryId = countryId;
            },
        },
        countryCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addSorting(Criteria.sort('position', 'ASC'));
            return criteria;
        },

        stateCriteria() {
            if (!this.task.countryId) {
                return null;
            }

            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('countryId', this.task.countryId));
            return criteria;
        },

        productCriteria(){
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('active',1));
        },

        tooltipSave() {
            if (this.acl.can('ict_task.editor')) {
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

        ...mapPropertyErrors('task', ['name','city']),
    },

    watch: {
        taskId() {
            this.createdComponent();
        },
        countryId: {
            immediate: true,
            handler(newId, oldId) {
                if (typeof oldId !== 'undefined') {
                    this.task.countryStateId = null;
                }
                // console.log(oldId);
                // console.log(newId);
                if (!this.countryId) {
                    this.country = null;
                    return Promise.resolve();
                }

                return this.countryRepository.get(this.countryId).then((country) => {
                    this.country = country;
                    this.getCountryStates();
                });
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            Shopware.ExtensionAPI.publishData({
                id: 'sw-task-detail__task',
                path: 'task',
                scope: this,
            });
            if (this.taskId) {
                this.loadEntityData();
                return;
            }

            Shopware.State.commit('context/resetLanguageToDefault');
            this.task = this.taskRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;

            const [taskResponse, customFieldResponse] = await Promise.allSettled([
                this.taskRepository.get(this.taskId),
            ]);

            if (taskResponse.status === 'fulfilled') {
                this.task = taskResponse.value;
            }

            if (taskResponse.status === 'rejected' ) {
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationLoadingDataErrorMessage',
                    ),
                });
            }

            this.isLoading = false;
        },

        abortOnLanguageChange() {
            return this.taskRepository.hasChanges(this.task);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.loadEntityData();
        },

        getCountryStates() {
            if (!this.country) {
                return Promise.resolve();
            }

            return this.countryStateRepository.search(this.stateCriteria).then((response) => {
                this.state = response;
            });
        },

        setMediaItem({ targetId }) {
            this.task.mediaId = targetId;
        },

        setMediaFromSidebar(media) {
            this.task.mediaId = media.id;
        },

        onUnlinkLogo() {
            this.task.mediaId = null;
        },

        openMediaSidebar() {
            this.$refs.mediaSidebarItem.openContent();
        },

        onDropMedia(dragData) {
            this.setMediaItem({ targetId: dragData.id });
        },

        onSave() {
            if (!this.acl.can('task.editor')) {
                return;
            }

            if (!this.entityValidationService.validate(this.task)) {
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

            this.taskRepository.save(this.task).then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if (this.taskId === null) {
                    this.$router.push({ name: 'sw.task.detail', params: { id: this.task.id } });
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
            this.$router.push({ name: 'sw.task.index' });
        },
    },
});
