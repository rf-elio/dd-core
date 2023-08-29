//import uuid from 'src/../test/_helper_/uuid';

Shopware.Component.extend('elio-search-export-create', 'elio-search-export-detail', {
    data() {
        return {
            isNew: true
        };
    },

    methods: {
        createdComponent() {
            this._fillSelectors();
            this.elio_search_export = this.exportRepository.create();
            this.elio_search_export.active = false;
            this.elio_search_export.name = this.$tc('elio-search-export.create.new-export-name');
            this.elio_search_export.baseCategories = new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []);
            this.elio_search_export.config = {
                export_product_categories: true,
                export_structure_categories: true,
                export_link_categories: true,
                trigger_import_search_data: false,
                trigger_import_suggest_data: false
            }
            this.elio_search_export.interval = '0 */4 * * *';
            this.elio_search_export.type = 'product';
            this.elio_search_export.format = 'csv';
            this.elio_search_export.active = true;
            this.elio_search_export.mapping = {};
            this.elio_search_export.downloadUsername = 'elio-search';
            this.elio_search_export.downloadPassword = uuid.get('en-GB');
            this.isLoading = false;
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'elio.search.export.detail', params: { id: this.elio_search_export.id } });
        },

        onSave() {
            this.$super('onSave');
        }
    }
});
