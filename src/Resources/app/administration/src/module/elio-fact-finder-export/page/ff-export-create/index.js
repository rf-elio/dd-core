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
            this.fillSelectors();

            this.ff_export = this.exportRepository.create();
            this.newId = this.ff_export.id;
            this.ff_export.active = false;
            this.ff_export.name = 'New_Export_Name';

            // todo: fetch it another way
            this.ff_export.interval = '0 */4 * * *';
            this.ff_export.type = 'product';
            this.ff_export.format = 'csv';
            this.ff_export.active = true;
            this.ff_export.mapping = '[]';

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