import template from './ff-export-list.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-export-list', {
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
        exportRepository() {
            return this.repositoryFactory.create('elio_ff_export');
        },
        exportColumns() {
            return this.getExportColumns();
        },
        exportCriteria() {
            var criteria = new Criteria();
            criteria.addAssociation('salesChannel');
            criteria.addAssociation('language');
            criteria.setPage(this.page);
            criteria.setLimit(this.limit);
            criteria.setTotalCountMode(2);
            criteria.addSorting(
                Criteria.sort('elio_ff_export.'+this.sortBy, this.sortDirection)
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

            return this.exportRepository.delete(id).then(() => {
                this.getList();
            });
        },

        async getList() {
            this.isLoading = true;
            var operator = this;

            try {
                var criteria = this.exportCriteria;
                this.activeFilterNumber = criteria.filters.length;
                await this.exportRepository.search(criteria, Shopware.Context.api)
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

        getExportColumns() {
            return [
                {
                    property: 'name',
                    routerLink: 'elio.factfinder.export.detail',
                    primary: true,
                    label: this.$tc('ff-export.list.columns.name'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'salesChannelId',
                    label: this.$tc('ff-export.list.columns.salesChannel'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'languageId',
                    label: this.$tc('ff-export.list.columns.language'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'active',
                    label: this.$tc('ff-export.list.columns.active'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'type',
                    label: this.$tc('ff-export.list.columns.type'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'format',
                    label: this.$tc('ff-export.list.columns.format'),
                    allowResize: false,
                    visible: true
                },
                {
                    property: 'lastGenerationFinishedAt',
                    label: this.$tc('ff-export.list.columns.lastGenerationFinishedAt'),
                    allowResize: false,
                    visible: true
                }
            ];
        }
    }
});
