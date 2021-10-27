import template from './ff-export-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-export-detail', {
    template: template,

    inject: [
        'repositoryFactory'
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
                    id: 'suggest',
                    name: 'suggest'
                },
                {
                    id: 'product',
                    name: 'product'
                },
                {
                    id: 'category',
                    name: 'category'
                },
                {
                    id: 'category_link',
                    name: 'category_link'
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
            ]
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
        exportMappingsRepository() {
            return this.repositoryFactory.create('elio_ff_export_mappings');
        },
        exportMappingsCriteria() {

        }
    },

    methods: {
        createdComponent() {
            this.loadEntityData();
        },

        loadEntityData() {
            this.isLoading = true;
            var operator = this;

            this.exportRepository.get(this.exportId, Shopware.Context.api, new Criteria())
                .then((currenExport) => {
                    operator.ff_export = currenExport;
                    operator.isLoading = false;
                })
                .catch(() => {
                    operator.isLoading = false;
                });
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        onSave() {
            this.isSaveSuccessful = false;
            this.isLoading = true;
            var operator = this;

            return this.exportRepository.save(this.ff_export).then(() => {
                operator.loadEntityData();
                operator.isLoading = false;
                operator.isSaveSuccessful = true;
            }).catch((exception) => {
                operator.createNotificationError({
                    message: this.$tc('ff.export.detail.messageSaveError')
                });
                operator.isLoading = false;
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'elio.factfinder.export.list' });
        }
    }
});