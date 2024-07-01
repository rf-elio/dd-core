import template from './elio-data-discovery-filter-restrictions-customfilters-detail.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Shopware.Component.register('elio-data-discovery-filter-restrictions-customfilters-detail', {
    template: template,

    inject: [
        'repositoryFactory',
        'entityValidationService'
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
        ...mapPropertyErrors('filter', [
            'technicalName',
            'label'
        ]),

        identifier() {
            return this.placeholder(this.filter, 'label');
        },

        filterRepository() {
            return this.repositoryFactory.create('elio_data_discovery_filter');
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('type', 'filter'))
            criteria.setTerm(this.term);
            return criteria;
        },

        useNaturalSorting() {
            return this.sortBy === 'elio_data_discovery_filter.label';
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
            let customFilterId = (this.filter && this.customFilterId === null) ? this.filter.id : this.customFilterId;

            this.filterRepository.get(customFilterId, Shopware.Context.api, this.defaultCriteria)
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

            if (!this.entityValidationService.validate(this.filter)) {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                });
                this.isLoading = false;
                return;
            }

            return this.filterRepository.save(this.filter).then(() => {
                this.loadEntityData();
                this.isLoading = false;
                this.isSaveSuccessful = true;
            }).catch((exception) => {
                this.createNotificationError({
                    message: this.$tc('elio-data-discovery.restrictions.filter.filters.messageSaveError')
                });
                this.isLoading = false;
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'elio.data.discovery.filter.restrictions.index.customfilters' });
        }
    }
});