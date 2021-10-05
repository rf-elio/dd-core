import template from './elio-plugin-config-detail.html.twig';
import './elio-plugin-config-detail.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('elio-plugin-config-detail', {
    template: template,

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
            return `${this.fullNamespace}.config`;
        },
        fullNamespace() {
            if (this.languageNameSpace !== '') {
                return this.languageNameSpace + '.' + this.namespace
            } else {
                return this.namespace;
            }
        }
    },

    watch: {
        languageId() {
            this.updateLanguage();
        }
    },

    methods: {
        onSave() {
            this.$refs.systemConfig.saveAll().then(() => {
                this.createNotificationSuccess({
                    message: this.$tc('sw-extension-store.component.sw-extension-config.messageSaveSuccess')
                });
            }).catch((err) => {
                this.createNotificationError({
                    message: err
                });
            });
        },

        onCreated() {
            this.languageId = Shopware.Context.api.languageId;
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.languageId = languageId;
            this.$refs.systemConfig.readAll();
        },

        updateLanguage() {
            this.languageNameSpace = '';
            console.log(this.languageNameSpace);
        },
    }
});