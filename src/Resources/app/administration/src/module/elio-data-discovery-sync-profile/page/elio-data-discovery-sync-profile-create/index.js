//import uuid from 'src/../test/_helper_/uuid';

Shopware.Component.extend('elio-data-discovery-sync-profile-create', 'elio-data-discovery-sync-profile-detail', {
    data() {
        return {
            isNew: true
        };
    },

    methods: {
        createdComponent() {
            this._fillSelectors();
            this._loadProfiles();
            this.elio_data_discovery_sync_profile = this.syncProfileRepository.create();
            this.elio_data_discovery_sync_profile.active = false;
            this.elio_data_discovery_sync_profile.name = this.$tc('elio-data-discovery-sync-profile.create.new-export-name');
            this.elio_data_discovery_sync_profile.baseCategories = new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []);
            this.elio_data_discovery_sync_profile.config = {
                sync_profile_product_categories: true,
                sync_profile_structure_categories: true,
                sync_profile_link_categories: true,
                trigger_import_search_data: false,
                trigger_import_suggest_data: false
            }
            this.elio_data_discovery_sync_profile.interval = '0 */4 * * *';

            this.elio_data_discovery_sync_profile.type = 'export';
            this.elio_data_discovery_sync_profile.dataType = 'product';
            this.elio_data_discovery_sync_profile.format = 'csv';
            this.elio_data_discovery_sync_profile.active = true;
            this.elio_data_discovery_sync_profile.mapping = {};
            this.elio_data_discovery_sync_profile.downloadUsername = 'elio-data-discovery';
            this.elio_data_discovery_sync_profile.downloadPassword = '';
            this.isLoading = false;
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({ name: 'elio.data.discovery.sync.profile.detail', params: { id: this.elio_data_discovery_export.id } });
        },

        onSave() {
            this.$super('onSave');
        }
    }
});
