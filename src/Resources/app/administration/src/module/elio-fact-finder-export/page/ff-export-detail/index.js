import template from './ff-export-detail.html.twig';
import './ff-export-detail.scss';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-export-detail', {
    template: template,

    inject: [
        'repositoryFactory',
        'ffExport'
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
            ff_export: null,
            isLoading: false,
            isSaveSuccessful: false,
            // todo: fetch somehow with ajax to server or from configs or from file
            typeList: [
                {
                    id: 'product',
                    name: 'product'
                },
                {
                    id: 'suggest',
                    name: 'suggest'
                }
            ],
            formatList: [
                {
                    id: 'csv',
                    name: 'csv'
                },
                {
                    id: 'xml',
                    name: 'xml'
                }
            ],
            languageIdsList: [],
            salesChannelIdsList: [],
            ff_export_mappings: [],
            ff_export_mappings_newId: 0,
            isMappingsEmpty: true,
            commandForce: false,
            status: {
                exists: false,
                location: ''
            },
            isGenerating: false,
            updateTimer: null,
            updateInterval: 3000
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        identifier() {
            return this.placeholder(this.ff_export, 'name');
        },
        exportRepository() {
            return this.repositoryFactory.create('elio_ff_export');
        },
        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },
        languageRepository() {
            return this.repositoryFactory.create('language');
        },
        command() {
            if(!this.ff_export) {
                return '...';
            }

            return 'bin/console elio-ff:export:generate ' + (this.commandForce ? '-f ' : '') + this.ff_export.id;
        },
        getDownloadUrl() {
            return this.ffExport.getDownloadUrl(this.exportId)
        }
    },

    methods: {
        createdComponent() {
            this.fillSelectors();
            this.loadEntityData();
            this.updateStatus();
        },

        fillSelectors() {
            var operator = this;
            this.salesChannelRepository.search(new Criteria, Shopware.Context.api)
                .then((salesChannels) => {
                    salesChannels.forEach((salesChannel) => {
                        operator.salesChannelIdsList.push({
                            id: salesChannel.id,
                            name: salesChannel.name
                        });
                    });
                });
            this.languageRepository.search(new Criteria, Shopware.Context.api)
                .then((languages) => {
                    languages.forEach((language) => {
                        operator.languageIdsList.push({
                            id: language.id,
                            name: language.name
                        });
                    });
                });
        },

        loadEntityData() {
            this.isLoading = true;
            var operator = this;

            this.exportRepository.get(this.exportId, Shopware.Context.api, new Criteria())
                .then((currenExport) => {
                    if (currenExport == null) {
                        operator.$router.push({ name: 'elio.factfinder.export.list' });
                    }
                    operator.ff_export = currenExport;
                    operator.ff_export_mappings = operator.getMappings();
                    operator.ff_export_mappings_newId = operator.ff_export_mappings.length;
                    operator.isLoading = false;
                })
                .catch((err) => {
                    console.log(err);
                    operator.isLoading = false;
                    operator.$router.push({ name: 'elio.factfinder.export.list' });
                });
        },

        /**
         * Updates the current status of this export
         */
        updateStatus() {
            this.ffExport.getStatus(this.exportId).then((response) => {
                this.status = response.data
            })
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        onSave() {
            this.ff_export.nextGenerationDueAt = null;
            this.isSaveSuccessful = false;
            this.isLoading = true;
            var operator = this;
            this.setMappings();

            return this.exportRepository.save(this.ff_export).then(() => {
                operator.loadEntityData();
                operator.isLoading = false;
                operator.isSaveSuccessful = true;
            }).catch((exception) => {
                operator.createNotificationError({
                    message: this.$tc('ff-export.detail.messageSaveError')
                });
                operator.isLoading = false;
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'elio.factfinder.export.list' });
        },

        onAddNewMapping() {
            if (this.isMappingsEmpty) {
                this.isMappingsEmpty = false;
            }
            this.ff_export_mappings.push({
                id: this.ff_export_mappings_newId,
                source: 'new_source',
                target: 'new_target'
            });
            this.ff_export_mappings_newId = this.ff_export_mappings_newId + 1;
        },

        onDeleteMapping(id) {
            var position = -1;
            this.ff_export_mappings.forEach((mapping, key) => {
                if (position === -1) {
                    if (mapping.id === id) {
                        position = key;
                    }
                }
            });
            if (position !== -1) {
                this.ff_export_mappings.splice(position, 1);
                if (this.ff_export_mappings.length === 0) {
                    this.isMappingsEmpty = true;
                }
            }
        },

        setMappings() {
            var mappings = [];
            // removing ids from saving
            this.ff_export_mappings.forEach((mapping) => {
                mappings.push(
                    {
                        source: mapping.source,
                        target: mapping.target
                    }
                );
            });
            this.ff_export.mapping = JSON.stringify(mappings);
        },

        getMappings() {
            var result = [];
            var mappings = [];
            try {
                mappings = JSON.parse(this.ff_export.mapping);
            } catch (err) {}
            var i = 0;
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
            window.open(this.ffExport.getDownloadUrl(this.exportId), '_blank');
        },

        onGenerate() {
            var operator = this;

            this.updateTimer = setTimeout(function requestStatus(){
                console.log('trying update status for export:' + operator.exportId);
                operator.updateStatus();
                if (operator.status.exists === true) {
                    clearTimeout(operator.updateTimer)
                    operator.isGenerating = false;
                } else {
                    operator.updateTimer = setTimeout(requestStatus, operator.updateInterval||3000);
                }
            }, operator.updateInterval||3000);

            this.isGenerating = true;
            this.ffExport.generate(this.exportId).then((responce) => {
                console.log(responce);
            }).catch((exception) => {
                operator.createNotificationError({
                    message: this.$tc('ff-export.detail.messageGeneratingError')
                });
                operator.isGenerating = false;
            });
        }
    }
});