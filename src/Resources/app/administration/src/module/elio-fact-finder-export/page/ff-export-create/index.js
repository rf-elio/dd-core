import uuid from 'src/../test/_helper_/uuid';

Shopware.Component.extend('ff-export-create', 'ff-export-detail', {
    data() {
        return {
            isNew: true
        };
    },

    methods: {
        createdComponent() {
            this._fillSelectors();
            this.ff_export = this.exportRepository.create();
            this.ff_export.active = false;
            this.ff_export.name = this.$tc('ff-export.create.new-export-name');
            this.ff_export.baseCategories = new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []);
            this.ff_export.config = {
                export_product_categories: true,
                export_structure_categories: true,
                export_link_categories: true,
                trigger_import_search_data: false,
                trigger_import_suggest_data: false
            }
            this.ff_export.interval = '0 */4 * * *';
            this.ff_export.type = 'product';
            this.ff_export.format = 'csv';
            this.ff_export.active = true;
            this.ff_export.mapping = {};
            this.ff_export.downloadUsername = 'ff';
            this.ff_export.downloadPassword = uuid.get('en-GB');
            this.isLoading = false;
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'elio.factfinder.export.detail', params: { id: this.ff_export.id } });
        },

        onSave() {
            this.$super('onSave');
        }
    }
});
