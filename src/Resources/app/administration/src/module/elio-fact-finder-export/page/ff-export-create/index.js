const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.extend('ff-export-create', 'ff-export-detail', {

    data() {
        return {
            isNew: true,
            newId: null
        };
    },

    methods: {
        createdComponent() {
            this.ff_export = this.exportRepository.create();
            this.newId = this.ff_export.id;
            this.ff_export.active = false;
            this.ff_export.name = 'New_Export_Name';

            // todo: fetch it another way
            this.ff_export.salesChannelId = '20e95b9683354988ac18cafc6cbb192b';
            this.ff_export.interval = '0 * * * *';
            this.ff_export.languageId = '2fbb5fe2e29a4d70aa5854ce7ce3e20b';
            this.ff_export.format = 'csv';

            this.isLoading = false;
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'elio.factfinder.export.detail', params: { id: this.newId } });
        },

        onSave() {
            this.$super('onSave');
        }
    }
});