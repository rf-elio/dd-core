import template from './ff-restrictions-index.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('ff-restrictions-index', {
    template: template,

    methods: {
        onSave() {
            if(this.$refs.routerView.$refs.ruler) {
                this.$refs.routerView.$refs.ruler.saveAll();
            }
        }
    }
});