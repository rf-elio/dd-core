import template from './elio-data-discovery-sorting-restrictions-customfilters.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('elio-data-discovery-sorting-restrictions-customfilters', {
    template: template,

    inject: [
        'repositoryFactory',
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
            filtersGroup: null,
            sortBy: 'label',
            sortDirection: 'ASC',
            isLoading: false,
            showDeleteModal: false
        };
    },

    computed: {
        filtersRepository() {
            return this.repositoryFactory.create('elio_data_discovery_filter');
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.useNaturalSorting));
            criteria.addFilter(Criteria.equals('elio_data_discovery_filter.isCustom', true));
            criteria.addFilter(Criteria.equals('type', 'sorting'))
            return criteria;
        },

        useNaturalSorting() {
            return this.sortBy === 'elio_data_discovery_filter.label';
        },

        filtersColumns() {
            return this.getFiltersColumns();
        },
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

            return this.filtersRepository.delete(id).then(() => {
                this.getList();
            });
        },

        getList() {
            this.isLoading = true;

            return this.filtersRepository.search(this.defaultCriteria).then((items) => {
                this.total = items.total;
                this.filtersGroup = items;
                this.isLoading = false;

                return items;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        getFiltersColumns() {
            return [{
                property: 'technicalName',
                label: 'elio-data-discovery.restrictions.sorting.customFilters.list.columnTechnicalName',
                routerLink: 'elio.data.discovery.sorting.restrictions.customFiltersDetail',
                inlineEdit: 'string',
                allowResize: false,
                primary: true
            }];
        }
    }
});