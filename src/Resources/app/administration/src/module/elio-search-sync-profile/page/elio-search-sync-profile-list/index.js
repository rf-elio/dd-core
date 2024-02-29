import template from './elio-search-sync-profile-list.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('elio-search-sync-profile-list', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            exports: null,
            sortBy: 'lastGenerationStartedAt',
            sortDirection: 'DESC',
            isLoading: false,
            activeFilterNumber: 0,
            page: 1,
            limit: 25,
            total: 1,
            showDeleteModal: false
        };
    },

    computed: {
        syncProfileRepository() {
            return this.repositoryFactory.create('elio_search_sync_profile');
        },
        exportColumns() {
            return this.getExportColumns();
        },
        exportCriteria() {
            var criteria = new Criteria();
            criteria.addAssociation('salesChannel');
            criteria.addAssociation('languages');
            criteria.setPage(this.page);
            criteria.setLimit(this.limit);
            criteria.setTotalCountMode(2);
            criteria.addSorting(
                Criteria.sort('elio_search_sync_profile.'+this.sortBy, this.sortDirection)
            );
            return criteria;
        }
    },

    created() {
        this.getList();
    },

    methods: {
        onDelete(id) {
            this.showDeleteModal = id;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete(id) {
            this.showDeleteModal = false;

            return this.syncProfileRepository.delete(id).then(() => {
                this.getList();
            });
        },

        async getList() {
            this.isLoading = true;
            var operator = this;

            try {
                var criteria = this.exportCriteria;
                this.activeFilterNumber = criteria.filters.length;
                await this.syncProfileRepository.search(criteria, Shopware.Context.api)
                    .then((exports) => {
                        operator.total = exports.total;
                        operator.exports = exports;
                        operator.isLoading = false;
                    }).catch(() => {
                        operator.isLoading = false;
                    });
            } catch (err) {
                console.log(err);
                this.isLoading = false;
            }
        },

        onPageChange({ page, limit }) {
            this.page = page;
            this.limit = limit;
            this.getList();
        },

        formatDate(dateToFormat) {
            return dateToFormat;
        },

        extractDataType(dataType) {
            return dataType.split('\\').pop();
        },

        getExportColumns() {
            return [
                {
                    property: 'name',
                    routerLink: 'elio.search.sync.profile.detail',
                    primary: true,
                    label: this.$tc('elio-search-sync-profile.list.columns.name'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'salesChannelId',
                    label: this.$tc('elio-search-sync-profile.list.columns.salesChannel'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'active',
                    label: this.$tc('elio-search-sync-profile.list.columns.active'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'profile',
                    label: this.$tc('elio-search-sync-profile.list.columns.profile'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'dataType',
                    label: this.$tc('elio-search-sync-profile.list.columns.dataType'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'lastGenerationFinishedAt',
                    label: this.$tc('elio-search-sync-profile.list.columns.lastGenerationFinishedAt'),
                    allowResize: false,
                    visible: true
                }
            ];
        }
    }
});
