import template from './sw-cms-el-config-edd-advisor-campaign.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-edd-advisor-campaign', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('edd-advisor-campaign');
        },

        onElementUpdate(element) {
            this.$emit('element-update', element);
        }
    }
})
