import template from './elio-search-filter-restrictions-customfilters-detail.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('elio-search-filter-restrictions-customfilters-detail', {
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
            isSaveSuccessful: false
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
            criteria.addFilter(Criteria.equals('type', 'filter'))
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

            return this.filterRepository.save(this.filter).then(() => {
                this.loadEntityData();
                this.isLoading = false;
                this.isSaveSuccessful = true;
            }).catch((exception) => {
                this.createNotificationError({
                    message: this.$tc('elio-search.restrictions.filter.filters.messageSaveError')
                });
                this.isLoading = false;
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'elio.search.filter.restrictions.index.customfilters' });
        }
    }
});