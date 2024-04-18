import template from './elio-data-discovery-sync-profile-detail.html.twig';
import './elio-data-discovery-sync-profile-detail.scss';

const {Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Shopware.Component.register('elio-data-discovery-sync-profile-detail', {
    template: template,
    inject: [
        'repositoryFactory',
        'elioDataDiscoverySyncProfile',
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
        exportId: {
            type: String,
            required: false,
            default: null
        }
    },

    data() {
        return {
            isNew: false,
            elio_data_discovery_sync_profile: null,
            isLoading: false,
            isSaveSuccessful: false,
            // todo: fetch somehow with ajax to server or from configs or from file
            profiles: [],
            type: '',
            features: {
                multiLanguageSupport: false
            },
            dataTypes: [],
            isMultiLanguageSupport: false,
            languageIdsList: [],
            categoryIdsList: [],
            salesChannelIdsList: [],
            elio_data_discovery_sync_profile_mappings: [],
            elio_data_discovery_sync_profile_newId: 0,
            isMappingsEmpty: true,
            isContent: false,
            elio_data_discovery_sync_profile_config: {},
            commandForce: false,
            status: {
                finished: false,
                location: ''
            },
            isGenerating: false,
            updateTimer: null,
            updateInterval: 3000,
            baseCategories: null,
            newLanguageId: null,
            currentLanguageId: null,
            languageId: null,
            languageCriteria: new Criteria,
            isExtensionsActive: true
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        ...mapPropertyErrors('elio_data_discovery_sync_profile', [
            'name',
            'profile',
            'dataType',
            'interval',
            'salesChannelId',
            'baseCategoryIds',
        ]),

        identifier() {
            return this.placeholder(this.elio_data_discovery_sync_profile, 'name');
        },
        syncProfileRepository() {
            return this.repositoryFactory.create('elio_data_discovery_sync_profile');
        },
        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },
        languageRepository() {
            return this.repositoryFactory.create('language');
        },
        categoryRepository() {
            return this.repositoryFactory.create('category');
        },
        command() {
            if (!this.elio_data_discovery_sync_profile) {
                return '...';
            }

            return 'bin/console elio-data-discovery:profiles:sync ' + (this.commandForce ? '-f ' : '') + this.elio_data_discovery_sync_profile.id;
        },
        getDownloadUrl() {
            return this.elioDataDiscoverySyncProfile.getDownloadUrl(
                this.exportId,
                this.elio_data_discovery_sync_profile.salesChannel.name,
                // this.elio_data_discovery_sync_profile.language.locale.code,
                this.elio_data_discovery_sync_profile.downloadUsername,
                this.elio_data_discovery_sync_profile.downloadPassword
            )
        }
    },

    methods: {
        createdComponent() {
            this._fillSelectors();
            this._loadEntityData();
            this._updateStatus();
            this._loadProfiles();
        },

        /**
         * filling selectors for sales channels and languages
         * @private
         */
        _fillSelectors() {
            const that = this;
            this.salesChannelRepository.search(new Criteria, Shopware.Context.api).then((salesChannels) => {
                salesChannels.forEach((salesChannel) => {
                    that.salesChannelIdsList.push({
                        id: salesChannel.id,
                        name: salesChannel.name
                    });
                });
            });

            this.baseCategories = new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []);
        },

        /**
         * Reloading entity data => cleaning unsaved changes
         * @private
         */
        _loadEntityData() {
            this.isLoading = true;
            const that = this;

            const criteria = new Criteria();
            criteria.addAssociation('salesChannel');
            criteria.addAssociation('languages');
            this.syncProfileRepository.get(this.exportId, Shopware.Context.api, criteria).then((currenExport) => {
                if (currenExport == null) {
                    that.$router.push({name: 'elio.data.discovery.sync.profile.list'});
                }

                that.elio_data_discovery_sync_profile = currenExport;
                that.elio_data_discovery_sync_profile_mappings = that._prepareSavedMapping();
                that.elio_data_discovery_sync_profile_mappings_newId = that.elio_data_discovery_sync_profile_mappings.length;
                that.isLoading = false;

                const criteria = new Criteria();
                criteria.setIds(currenExport.baseCategoryIds);
                that.prepareLanguageCriteria(currenExport.salesChannelId);

                if (that.elio_data_discovery_sync_profile.languages.first()) {
                    that.languageId = that.elio_data_discovery_sync_profile.languages.first().id
                }

                if (currenExport.baseCategoryIds && currenExport.baseCategoryIds.length > 0) {
                    const criteria = new Criteria();
                    criteria.setIds(currenExport.baseCategoryIds);

                    return this.categoryRepository.search(criteria, Shopware.Context.api).then((categories) => {
                        this.baseCategories = categories;
                    });
                }
            })
            .catch((err) => {
                console.log(err);
                that.isLoading = false;
                that.$router.push({name: 'elio.data.discovery.sync.profile.list'});
            });
        },

        /**
         * safety loading only timings data and not cleaning the unsaved changes for entity
         * @private
         */
        _loadTimingsData() {
            const that = this;
            this.syncProfileRepository.get(this.exportId, Shopware.Context.api, new Criteria())
                .then((currenExport) => {
                    if (currenExport != null) {
                        that.elio_data_discovery_sync_profile.lastGenerationStartedAt = currenExport.lastGenerationStartedAt;
                        that.elio_data_discovery_sync_profile.lastGenerationFinishedAt = currenExport.lastGenerationFinishedAt;
                        that.elio_data_discovery_sync_profile.nextGenerationDueAt = currenExport.nextGenerationDueAt;
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
        },

        _loadProfiles() {
            this.elioDataDiscoverySyncProfile.getProfiles().then((response) => {
                let isFound = false;

                Object.entries(response.profiles).forEach(([key, data]) => {
                    isFound = true;
                    this.isExtensionsActive = true;
                    this.profiles.push({
                        id: key,
                        name: data['name'],
                        dataTypes: data['dataTypes'],
                        features: data['features']
                    })
                });

                if (!isFound) {
                    this.isExtensionsActive = false;
                }

                this.onChangeProfile(this.profiles[0].id);
            });
        },

        onChangeProfile(key) {
            const dataTypes = [];
            const type = '';
            const profile = this.profiles.find((profile) => profile.id = key);

            profile.dataTypes.forEach((dataType) => {
                dataTypes.push({id: dataType, name: dataType.split('\\').pop()});
            });

            this.type = profile.type;
            this.dataTypes = dataTypes;
            this.features = profile.features;
        },

        async onChangeLanguage(languageId) {
            const language = await this.languageRepository.get(languageId, Shopware.Context.api);
            const languageCollectionn = new EntityCollection(this.elio_data_discovery_sync_profile.languages.source, 'language', Shopware.Context.api);
            languageCollectionn.add(language);
            this.elio_data_discovery_sync_profile.languages = languageCollectionn;
        },

        onSalesChannelChange(salesChannelId) {
            this.elio_data_discovery_sync_profile.languages = new EntityCollection(this.elio_data_discovery_sync_profile.languages.source, 'language', Shopware.Context.api);
            this.languageId = null;
            this.prepareLanguageCriteria(salesChannelId);
        },

        /**
         * Updates the current status of this export
         * @private
         */
        _updateStatus() {
            this.elioDataDiscoverySyncProfile.getStatus(this.exportId).then((response) => {
                this.status = response.data
            })
        },

        /**
         * Updates the base categories assigned to the export
         * @param categories
         */
        onChangeBaseCategory(categories) {
            this.elio_data_discovery_sync_profile.baseCategoryIds = categories.getIds();
            this.baseCategories = categories;
        },

        /**
         * Saves the current export profile
         * @returns {*}
         */
        onSave() {
            this.elio_data_discovery_sync_profile.nextGenerationDueAt = null;
            this.isSaveSuccessful = false;
            this.isLoading = true;
            const that = this;
            this._prepareMappingForSaving();

            if (this.elio_data_discovery_sync_profile.languages.length === 0) {
                this.createNotificationError({
                    message: this.$tc('elio-data-discovery-sync-profile.detail.messageSaveError', 0, {
                        error: 'Please select at least one language'
                    })
                });
                this.isLoading = false;
                return;
            }

            if (!this.entityValidationService.validate(this.elio_data_discovery_sync_profile)) {
                const titleSaveError = this.$tc('global.default.error');
                const messageSaveError = this.$tc(
                    'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid',
                );

                this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError,
                });
                this.isLoading = false;
                return;
            }

            return this.syncProfileRepository.save(this.elio_data_discovery_sync_profile).then(() => {
                that._loadEntityData();
                that.isLoading = false;
                that.isSaveSuccessful = true;
            }).catch((exception) => {
                that.createNotificationError({
                    message: this.$tc('elio-data-discovery-sync-profile.detail.messageSaveError', 0, {
                        error: exception.message, // todo: add proper error message
                    })
                });
                that.isLoading = false;
                throw exception;
            });
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;
        },

        onCancel() {
            this.$router.push({name: 'elio.data.discovery.sync.profile.list'});
        },

        /**
         * On click on add new mapping button
         */
        onAddNewMapping() {
            if (this.isMappingsEmpty) {
                this.isMappingsEmpty = false;
            }
            this.elio_data_discovery_sync_profile_mappings.push({
                id: this.elio_data_discovery_sync_profile_mappings_newId,
                source: this.$tc('elio-data-discovery-sync-profile.detail.mappingSourcePlaceholder'),
                target: 'new_target'
            });
            this.elio_data_discovery_sync_profile_mappings_newId = this.elio_data_discovery_sync_profile_mappings_newId + 1;
        },

        /**
         * On button delete mapping click
         */
        onDeleteMapping(id) {
            let position = -1;
            this.elio_data_discovery_sync_profile_mappings.forEach((mapping, key) => {
                if (position === -1) {
                    if (mapping.id === id) {
                        position = key;
                    }
                }
            });
            if (position !== -1) {
                this.elio_data_discovery_sync_profile_mappings.splice(position, 1);
                if (this.elio_data_discovery_sync_profile_mappings.length === 0) {
                    this.isMappingsEmpty = true;
                }
            }
        },

        /**
         * sets mappings to save in database
         * @private
         */
        _prepareMappingForSaving() {
            const mappings = [];
            // removing ids from saving
            this.elio_data_discovery_sync_profile_mappings.forEach((mapping) => {
                mappings.push(
                    {
                        source: mapping.source,
                        target: mapping.target
                    }
                );
            });
            this.elio_data_discovery_sync_profile.mapping = mappings;
        },

        /**
         * fetches the mappings from export-entity
         * @private
         */
        _prepareSavedMapping() {
            const mappings = Object.values(this.elio_data_discovery_sync_profile.mapping);
            let i = 0, result = [];
            mappings.forEach((mapping) => {
                result.push(
                    {
                        id: i,
                        source: mapping.source,
                        target: mapping.target
                    }
                );
                i++;
            });

            if (mappings.length >= 1) {
                this.isMappingsEmpty = false;
            }
            return result;
        },

        /**
         * Opens the download in a new window
         */
        onDownloadExport() {
            window.open(this.elioDataDiscoverySyncProfile.getDownloadUrl(
                this.exportId,
                this.elio_data_discovery_sync_profile.salesChannel.name,
                this.elio_data_discovery_sync_profile.language.locale.code,
                this.elio_data_discovery_sync_profile.downloadUsername,
                this.elio_data_discovery_sync_profile.downloadPassword
            ), '_blank');
        },

        /**
         * On click on generate button
         */
        onGenerate() {
            const that = this;
            this.updateTimer = setTimeout(function requestStatus() {
                that._updateStatus();
                if (that.status.finished === true) {
                    clearTimeout(that.updateTimer);
                    that.isGenerating = false;
                    that.createNotificationSuccess({
                        title: that.$tc('global.default.success'),
                        message: that.$tc('elio-data-discovery-sync-profile.detail.messageGeneratingSuccess')
                    });
                    that._loadTimingsData();
                } else {
                    that.updateTimer = setTimeout(requestStatus, that.updateInterval || 3000);
                }
            }, that.updateInterval || 3000);

            this.isGenerating = true;
            this.elioDataDiscoverySyncProfile.generate(this.exportId).then((responce) => {
                that.elio_data_discovery_sync_profile.lastGenerationStartedAt = Date.now();
            }).catch((exception) => {
                that.createNotificationError({
                    message: this.$tc('elio-data-discovery-sync-profile.detail.messageGeneratingError', 0, {
                        error: exception.message, // todo: add proper error message
                    })
                });
                that.isGenerating = false;
            });
        },

        /**
         * Format date in twig
         */
        formatDate(dateToFormat) {
            return dateToFormat;
        },

        prepareLanguageCriteria(salesChannelId = null) {
            const criteria = new Criteria(1, 25);

            if (salesChannelId) {
                criteria.addFilter(Criteria.equals('salesChannels.id', salesChannelId));
            }

            this.languageCriteria = criteria;
        }
    }
});
