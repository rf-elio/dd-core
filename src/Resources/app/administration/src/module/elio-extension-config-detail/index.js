import template from './elio-plugin-config-detail.html.twig';
import './elio-plugin-config-detail.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Shopware.Component.override('sw-extension-config', {
    template: template,

    inject: [
        'repositoryFactory'
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        namespace: {
            type: String,
            required: false,
            default: 'FactFinder'
        }
    },

    created() {
        this.onCreated();
    },

    data() {
        return {
            salesChannelId: null,
            languageId: '',
            languageNameSpace: '',
        };
    },

    computed: {
        domain() {
            return `${this.namespace}.config`;
        },
        languageRepository() {
            return this.repositoryFactory.create('language');
        },
        defaultLanguageCriteria() {
            var criteria = new Criteria();
            criteria.addAssociation('locale');
            criteria.addFilter(Criteria.equals('language.id', this.languageId));
            return criteria;
        }
    },

    watch: {
        languageId() {
            this.updateLanguage();
        },
        languageNameSpace() {
            this.updateActualConfigData();
        }
    },

    methods: {
        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.languageId = languageId;
        },

        onCreated() {
            this.languageId = Shopware.Context.api.languageId;
        },

        async updateLanguage() {
            var operator = this;
            await this.languageRepository.search(this.defaultLanguageCriteria, Shopware.Context.api).then(languages => {
                if (languages.length > 0) {
                    operator.languageNameSpace = languages[0].locale.code;
                }
            });
        },

        updateActualConfigData() {
            console.log('updateActualConfigData(' + this.languageNameSpace + ')');
            var operator = this;

            if (this.$refs.systemConfig.config) {
                this.$refs.systemConfig.config.forEach((keyValue) => {
                    keyValue.elements.forEach((configElem) => {
                        var splited = configElem['name'].split('.');
                        var newName = '';
                        splited.forEach((entry, i) => {
                            newName += (i > 0 ? '.' : '') + (i === (splited.length - 1) ? operator.languageNameSpace + '_' + entry.split('_')[entry.split('_').length-1] : entry);
                        });
                        configElem['name'] = newName;
                    });
                });
            }
        },

        onSave() {
            console.log(this.$refs.systemConfig.actualConfigData[null]);
            console.log(this.$refs.systemConfig.config);

            this.$refs.systemConfig.saveAll().then(() => {
                this.createNotificationSuccess({
                    message: this.$tc('sw-extension-store.component.sw-extension-config.messageSaveSuccess')
                });
            }).catch((err) => {
                this.createNotificationError({
                    message: err
                });
            });
        }
    }
});

Shopware.Template.register('sw-extension-config', template);