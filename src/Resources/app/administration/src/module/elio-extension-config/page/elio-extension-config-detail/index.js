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

    data() {
        return {
            salesChannelId: null,
            languageId: null
        };
    },

    computed: {
        domain() {
            return `${this.namespace}.config`;
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
        }
    }

});