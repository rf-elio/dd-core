import template from './elio-search-sorting-restrictions-customfilters-detail.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('elio-search-sorting-restrictions-customfilters-detail', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    shortcuts: {
        'SYSTEMKEY+S': {
            active() {
                return true
            },
            method: 'onSave'
        },
        ESCAPE: 'onCancel'
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    props: {
        customFilterId: {
            type: String,
            required: false,
            default: null
        }
    },

    data() {
        return {
            filter: null,
            isLoading: false,
            isSaveSuccessful: false,
            isAscSortDirection: false
        };
    },

    computed: {
        identifier() {
            return this.placeholder(this.filter, 'propertyName');
        },

        filterRepository() {
            return this.repositoryFactory.create('elio_search_filter');
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('type', 'sorting'));
            criteria.setTerm(this.term);
            return criteria;
        },

        useNaturalSorting() {
            return this.sortBy === 'elio_search_filter.propertyName';
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadEntityData();
        },

        loadEntityData() {
            this.isLoading = true;

            this.filterRepository.get(this.customFilterId, Shopware.Context.api, this.defaultCriteria)
                .then((currentFilter) => {
                    const technicalNameParts = currentFilter.technicalName.split(':')
                    currentFilter.technicalName = technicalNameParts[0];
                    if (technicalNameParts[1] === 'asc') {
                        this.isAscSortDirection = true;
                    }

                    this.filter = currentFilter;
                    this.isLoading = false;
                }).catch(() => {
                this.isLoading = false;
            });
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        abortOnLanguageChange() {
            return this.filterRepository.hasChanges(this.filter);
        },

        onChangeLanguage() {
            this.loadEntityData();
        },

        onSave() {
            this.isSaveSuccessful = false;
            this.isLoading = true;

            if (this.filter.displayedByDefault) {
                this.removeDefaultForFilters();
            }

            if (this.isAscSortDirection === true) {
                this.filter.technicalName += ':asc';
            } else {
                this.filter.technicalName += ':desc';
            }

            return this.filterRepository.save(this.filter).then(() => {
                this.loadEntityData();
                this.isLoading = false;
                this.isSaveSuccessful = true;
            }).catch((exception) => {
                this.createNotificationError({
                    message: this.$tc('elio-search.restrictions.sorting.filters.messageSaveError')
                });
                this.isLoading = false;
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'elio.search.sorting.restrictions.index.customfilters' });
        },

        async removeDefaultForFilters() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('type', 'sorting'));
            criteria.addFilter(Criteria.equals('displayedByDefault', true));
            await this.filterRepository.search(criteria, Shopware.Context.api)
            .then(filters => {
                filters.forEach(filter => {
                    filter.displayedByDefault = false;
                    this.filterRepository.save(filter)
                });
            })
        }
    }
});