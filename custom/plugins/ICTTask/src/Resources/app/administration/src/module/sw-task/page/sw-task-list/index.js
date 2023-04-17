/*
 * @package inventory
 */

import template from './sw-task-list.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Component.register('sw-task-list', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            tasks: null,
            isLoading: true,
            sortBy: 'name',
            sortDirection: 'ASC',
            total: 0,
            searchConfigEntity: 'ict_task',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        taskRepository() {
            return this.repositoryFactory.create('ict_task');
        },

        taskColumns() {
            return [{
                property: 'name',
                dataIndex: 'name',
                allowResize: true,
                routerLink: 'sw.task.detail',
                label: 'sw-task.list.columnName',
                inlineEdit: 'string',
                primary: true,
            }, {
                property: 'city',
                label: 'sw-task.list.columnCity',
                inlineEdit: 'string',
            },{
                property: 'active',
                label: 'sw-task.list.columnActive',
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center',
            },{
                property: 'country.name',
                label: 'sw-task.list.columnCountry',
                inlineEdit: 'string'
            },{
                property: 'countryState.name',
                label: 'sw-task.list.columnState',
                inlineEdit: 'string'
            },{
                property: 'product.name',
                label: 'sw-task.list.columnProduct',
                inlineEdit: 'string',
                visible: false,
                useCustomSort: true,
            }];
        },

        taskCriteria() {
            const taskCriteria = new Criteria(this.page, this.limit);

            taskCriteria.setTerm(this.term);
            taskCriteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            taskCriteria.addAssociation('country');
            taskCriteria.addAssociation('countryState');
            taskCriteria.addAssociation('product');

            return taskCriteria;
        },
    },

    methods: {
        onChangeLanguage(languageId) {
            this.getList(languageId);
        },

        async getList() {
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.taskCriteria);

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;
                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.taskRepository.search(criteria)
                .then(searchResult => {
                    this.tasks = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        updateTotal({ total }) {
            this.total = total;
        },
    },
});
