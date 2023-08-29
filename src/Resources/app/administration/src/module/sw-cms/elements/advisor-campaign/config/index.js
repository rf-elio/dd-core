import template from './sw-cms-el-config-elio-search-advisor-campaign.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-elio-search-advisor-campaign', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('elio-search-advisor-campaign');
        },

        onElementUpdate(element) {
            this.$emit('element-update', element);
        }
    }
})
