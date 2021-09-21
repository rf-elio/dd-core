import template from './ff-restrictions-customfilters-create.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.extend('ff-restrictions-customfilters-create', 'ff-restrictions-customfilters-detail', {
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
            this.filter.propertyName = 'propertyName';
            this.newId = this.filter.id;

            this.isLoading = false;
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'elio.factfinder.restrictions.customFiltersDetail', params: { id: this.newId } });
        },

        onSave() {
            this.$super('onSave');
        }
    }
});