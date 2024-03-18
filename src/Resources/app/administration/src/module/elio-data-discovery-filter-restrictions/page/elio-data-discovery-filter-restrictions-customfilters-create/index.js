import template from './elio-data-discovery-filter-restrictions-customfilters-create.html.twig';

Shopware.Component.extend('elio-data-discovery-filter-restrictions-customfilters-create', 'elio-data-discovery-filter-restrictions-customfilters-detail', {
    template: template,

    data() {
        return {
            newId: null
        };
    },

    methods: {
        createdComponent() {
            if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                Shopware.Context.api.languageId = Shopware.Context.api.systemLanguageId;
            }

            this.filter = this.filterRepository.create();
            this.filter.isCustom = true;
            this.filter.technicalName = 'technicalName'
            this.filter.propertyName = 'propertyName';
            this.filter.type = 'filter'
            this.newId = this.filter.id;

            this.isLoading = false;
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'elio.search.filter.restrictions.customFiltersDetail', params: { id: this.newId } });
        },

        onSave() {
            this.$super('onSave');
        }
    }
});