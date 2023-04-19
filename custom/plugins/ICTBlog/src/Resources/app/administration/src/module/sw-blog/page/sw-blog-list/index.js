/*
 * @package inventory
 */
import template from './sw-blog-list.html.twig';
const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-blog-list',{
    template,

    inject:['repositoryFactory','acl'],

    mixins:[
        Mixin.getByName('listing'),
    ],

    data(){
        return{
            blogs:null,
            isLoading:true,
            sortBy:'name',
            sortDirection:'ASC',
            total:0,
            searchConfigEntity:'ict_blog'
        };
    },
    metaInfo(){
        return{
            title:this.$createTitle(),
        }
    },
    computed:{
        blogRepository(){
            return this.repositoryFactory.create('ict_blog');
        },

        blogColumns(){
            return[
                {
                    property: 'name',
                    dataIndex: 'name',
                    allowResize: true,
                    routerLink: 'sw.blog.detail',
                    label: 'sw-blog.list.columnName',
                    inlineEdit: 'string',
                    primary: true,
                },{
                    property: 'description',
                    label: 'sw-blog.list.columnDescription',
                    inlineEdit: 'string',
                },{
                    property: 'active',
                    label: 'sw-blog.list.columnActive',
                    inlineEdit: 'boolean',
                    allowResize: true,
                    align: 'center',
                },{
                    property: 'releaseDate',
                    label: 'sw-blog.list.columnReleaseDate',
                    inlineEdit: 'string'
                },{
                    property: 'author',
                    label: 'sw-blog.list.columnAuthor',
                    inlineEdit: 'string'
                }
            ];
        },

        blogCriteria(){
            const blogCriteria = new Criteria(this.page,this.limit);

            blogCriteria.setTerm(this.term);
            blogCriteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            // blogCriteria.addAssociation('product');

            return blogCriteria;
        }
    },

    methods:{
        onChangeLanguage(languageId) {
            this.getList(languageId);
        },

        async getList(){
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.blogCriteria);

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;
                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.blogRepository.search(criteria)
                .then(searchResult => {
                    this.blogs = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        updateTotal({ total }) {
            this.total = total;
        },
    }
});
