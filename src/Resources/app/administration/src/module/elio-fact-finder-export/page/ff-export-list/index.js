import template from './ff-export-list.html.twig';

const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-export-list', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            exports: [],
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

        getExportColumns() {
            return [
                {
                    property: 'id',
                    label: this.$tc('ff.export.list.columns.id'),
                    routerLink: 'elio.factfinder.export.detail',
                    allowResize: false,
                    primary: true
                },
                {
                    property: 'name',
                    label: this.$tc('ff.export.list.columns.name'),
                    allowResize: false,
                },
                {
                    property: 'active',
                    label: this.$tc('ff.export.list.columns.active'),
                    allowResize: false,
                },
                {
                    property: 'type',
                    label: this.$tc('ff.export.list.columns.type'),
                    allowResize: false,
                },
                {
                    property: 'format',
                    label: this.$tc('ff.export.list.columns.format'),
                    allowResize: false,
                },
                {
                    property: 'lastGenerationFinishedAt',
                    label: this.$tc('ff.export.list.columns.lastGenerationFinishedAt'),
                    allowResize: false,
                }
            ];
        }
    }
});