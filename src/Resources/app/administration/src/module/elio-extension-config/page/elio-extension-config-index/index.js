import template from './elio-plugin-config-index.html.twig';

const { Component, Template } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.extend('elio-plugin-config-index', 'sw-extension-my-extensions-listing', {
    template: template,

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            filterByActiveState: true,
            sortingOption: 'updated-at'
        };
    },

    computed: {
        isAppRoute() {
            return this.$route.name === 'elio.extension.config.index';
        },

        isThemeRoute() {
            return false;
        },
    },

    methods: {
        changeActiveState(value) {
        },
    }
});

// extend method removing our template, and sw-extension-my-extensions-listing.template has no any blocks to override
Shopware.Template.register('elio-plugin-config-index', template);