import template from './elio-search-export-detail.html.twig';
import './elio-search-export-detail.scss';

const {Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Shopware.Component.register('elio-search-export-detail', {
    template: template,
    inject: [
        'repositoryFactory',
        'elioSearchExport'
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
            elio_search_export: null,
            isLoading: false,
            isSaveSuccessful: false,
            // todo: fetch somehow with ajax to server or from configs or from file
            typeList: [
                {id: 'product', name: 'product'},
                {id: 'content', name: 'content'}
            ],
            formatList: [
                {id: 'csv', name: 'csv'},
                // {id: 'xml', name: 'xml'}
            ],
            languageIdsList: [],
            categoryIdsList: [],
            salesChannelIdsList: [],
            elio_search_export_mappings: [],
            elio_search_export_mappings_newId: 0,
            isMappingsEmpty: true,
            isContent: false,
            elio_search_export_config: {},
            commandForce: false,
            status: {
                exists: false,
                location: ''
            },
            isGenerating: false,
            updateTimer: null,
            updateInterval: 3000,
            baseCategories: null
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        identifier() {
            return this.placeholder(this.elio_search_export, 'name');
        },
        exportRepository() {
            return this.repositoryFactory.create('elio_search_export');
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
            if (!this.elio_search_export) {
                return '...';
            }

            return 'bin/console elio-elio-search:export:generate ' + (this.commandForce ? '-f ' : '') + this.elio_search_export.id;
        },
        getDownloadUrl() {
            return this.elioSearchExport.getDownloadUrl(
                this.exportId,
                this.elio_search_export.salesChannel.name,
                this.elio_search_export.language.locale.code,
                this.elio_search_export.downloadUsername,
                this.elio_search_export.downloadPassword
            )
        }
    },

    methods: {
        createdComponent() {
            this._fillSelectors();
            this._loadEntityData();
            this._updateStatus();
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
            this.languageRepository.search(new Criteria, Shopware.Context.api).then((languages) => {
                languages.forEach((language) => {
                    that.languageIdsList.push({
                        id: language.id,
                        name: language.name
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
            criteria.addAssociation('language.locale');
            this.exportRepository.get(this.exportId, Shopware.Context.api, criteria).then((currenExport) => {
                if (currenExport == null) {
                    that.$router.push({name: 'elio.search.export.list'});
                }

                that.elio_search_export = currenExport;
                if (that.elio_search_export.type === 'content') {
                    that.isContent = true;
                }
                that.elio_search_export_mappings = that._prepareSavedMapping();
                that.elio_search_export_mappings_newId = that.elio_search_export_mappings.length;
                that.isLoading = false;

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
                that.$router.push({name: 'elio.search.export.list'});
            });
        },

        /**
         * safety loading only timings data and not cleaning the unsaved changes for entity
         * @private
         */
        _loadTimingsData() {
            const that = this;
            this.exportRepository.get(this.exportId, Shopware.Context.api, new Criteria())
                .then((currenExport) => {
                    if (currenExport != null) {
                        that.elio_search_export.lastGenerationStartedAt = currenExport.lastGenerationStartedAt;
                        that.elio_search_export.lastGenerationFinishedAt = currenExport.lastGenerationFinishedAt;
                        that.elio_search_export.nextGenerationDueAt = currenExport.nextGenerationDueAt;
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
        },

        /**
         * Updates the current status of this export
         * @private
         */
        _updateStatus() {
            this.elioSearchExport.getStatus(this.exportId).then((response) => {
                this.status = response.data
            })
        },

        /**
         * Updates the base categories assigned to the export
         * @param categories
         */
        onChangeBaseCategory(categories) {
            this.elio_search_export.baseCategoryIds = categories.getIds();
            this.baseCategories = categories;
        },

        /**
         * Saves the current export profile
         * @returns {*}
         */
        onSave() {
            this.elio_search_export.nextGenerationDueAt = null;
            this.isSaveSuccessful = false;
            this.isLoading = true;
            const that = this;
            this._prepareMappingForSaving();

            return this.exportRepository.save(this.elio_search_export).then(() => {
                that._loadEntityData();
                that.isLoading = false;
                that.isSaveSuccessful = true;
            }).catch((exception) => {
                that.createNotificationError({
                    message: this.$tc('elio-search-export.detail.messageSaveError', 0, {
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
            this.$router.push({name: 'elio.search.export.list'});
        },

        /**
         * On click on add new mapping button
         */
        onAddNewMapping() {
            if (this.isMappingsEmpty) {
                this.isMappingsEmpty = false;
            }
            this.elio_search_export_mappings.push({
                id: this.elio_search_export_mappings_newId,
                source: this.$tc('elio-search-export.detail.mappingSourcePlaceholder'),
                target: 'new_target'
            });
            this.elio_search_export_mappings_newId = this.elio_search_export_mappings_newId + 1;
        },

        /**
         * On button delete mapping click
         */
        onDeleteMapping(id) {
            let position = -1;
            this.elio_search_export_mappings.forEach((mapping, key) => {
                if (position === -1) {
                    if (mapping.id === id) {
                        position = key;
                    }
                }
            });
            if (position !== -1) {
                this.elio_search_export_mappings.splice(position, 1);
                if (this.elio_search_export_mappings.length === 0) {
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
            this.elio_search_export_mappings.forEach((mapping) => {
                mappings.push(
                    {
                        source: mapping.source,
                        target: mapping.target
                    }
                );
            });
            this.elio_search_export.mapping = mappings;
        },

        /**
         * fetches the mappings from export-entity
         * @private
         */
        _prepareSavedMapping() {
            const mappings = Object.values(this.elio_search_export.mapping);
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
            window.open(this.elioSearchExport.getDownloadUrl(
                this.exportId,
                this.elio_search_export.salesChannel.name,
                this.elio_search_export.language.locale.code,
                this.elio_search_export.downloadUsername,
                this.elio_search_export.downloadPassword
            ), '_blank');
        },

        /**
         * On click on generate button
         */
        onGenerate() {
            const that = this;
            this.updateTimer = setTimeout(function requestStatus() {
                that._updateStatus();
                if (that.status.exists === true) {
                    clearTimeout(that.updateTimer);
                    that.isGenerating = false;
                    that.createNotificationSuccess({
                        title: that.$tc('global.default.success'),
                        message: that.$tc('elio-search-export.detail.messageGeneratingSuccess')
                    });
                    that._loadTimingsData();
                } else {
                    that.updateTimer = setTimeout(requestStatus, that.updateInterval || 3000);
                }
            }, that.updateInterval || 3000);

            this.isGenerating = true;
            this.elioSearchExport.generate(this.exportId).then((responce) => {
                console.log(responce);
                that.elio_search_export.lastGenerationStartedAt = Date.now();
            }).catch((exception) => {
                that.createNotificationError({
                    message: this.$tc('elio-search-export.detail.messageGeneratingError', 0, {
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
        }
    }
});
