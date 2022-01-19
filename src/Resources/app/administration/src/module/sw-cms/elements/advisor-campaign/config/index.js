import template from './sw-cms-el-config-ff-advisor-campaign.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-ff-advisor-campaign', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created () {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('ff-advisor-campaign');
        },

        onElementUpdate(element) {
            this.$emit('element-update', element);
        }
    }
})
